<?php

use Kirby\Cms\Content;

require_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('sylvainjule/oembed', [
	'fields' => array(
		'oembed' => require_once __DIR__ . '/lib/oembed.php',
	),
    'fieldMethods' => array(
        'toEmbed' => function($field) {
            // allows if($embed = $page->myfield()->toEmbed()) { echo $embed->code() }
            if($field->isEmpty() || !count(Yaml::decode($field->value)) || empty($field->yaml()['media'])) {
                return null;
            }
            $content = new Content($field->yaml()['media'], $field->parent());
            return $content;
        },
    ),
    'api' => array(
        'routes' => function ($kirby) {
            return [
                [
                    'pattern' => 'kirby-oembed/get-data',
                    'action' => function() {
                        $response = [];
                        $url = get('url');

                        if(!V::url($url)) {
                            $response['status'] = 'error';
                            $response['error']  = 'The $url variable is not an url';
                        }
                        else {
                            try {
                                $dispatcher = new Embed\Http\CurlDispatcher();
                                $options = \Embed\Embed::$default_config;
                                $options['min_image_width'] = 60;
                                $options['min_image_height'] = 60;
                                $options['html']['max_images'] = 10;
                                $options['html']['external_images'] = false;

                                $media = Embed\Embed::create($url, $options, $dispatcher);

                                $response['status'] = 'success';
                                $response['data']   = array(
                                    'title'         => $media->title,
                                    'description'   => $media->description,
                                    'url'           => $media->url,
                                    'type'          => $media->type,
                                    'tags'          => $media->tags,
                                    'image'         => $media->image,
                                    'imageWidth'    => $media->imageWidth,
                                    'imageHeight'   => $media->imageHeight,
                                    'images'        => $media->images,
                                    'code'          => $media->code,
                                    'feeds'         => $media->feeds,
                                    'width'         => $media->width,
                                    'height'        => $media->height,
                                    'aspectRatio'   => $media->aspectRatio,
                                    'authorName'    => $media->authorName,
                                    'authorUrl'     => $media->authorUrl,
                                    'providerIcon'  => $media->providerIcon,
                                    'providerIcons' => $media->providerIcons,
                                    'providerName'  => $media->providerName,
                                    'providerUrl'   => $media->providerUrl,
                                    'publishedTime' => $media->publishedTime,
                                    'license'       => $media->license,
                                );
                            }
                            catch (Exception $e) {
                                $response['status'] = 'error';
                                $response['error']  = $e->getMessage();
                            }
                        }

                        return $response;
                    }
                ],
            ];
        }
    )
]);
