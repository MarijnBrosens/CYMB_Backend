<?php
return [
    'debug' => false,
    'api' => [
        'basicAuth' => true
    ],
    'routes' => [
        [
            'pattern' => 'rest/(:all)',
            'method'  => 'GET',
            'env'     => 'api',
            'action'  => function ($path = null) {
                $kirby = new Kirby([
                    'roots' => [
                        'index'    => dirname(dirname(__DIR__)) . '/public',
                        'base'     => $base    = dirname(dirname(__DIR__)),
                        'content'  => $base . '/content',
                        'site'     => dirname(dirname(__DIR__)) . '/site',
                        'storage'  => $storage = $base . '/storage',
                        'accounts' => $storage . '/accounts',
                        'cache'    => $storage . '/cache',
                        'sessions' => $storage . '/sessions',
                    ]
                ]);

                $request = $kirby->request();
                
                $render = $kirby->api()->render($path, $this->method(), [
                    'body'    => $request->body()->toArray(),
                    'headers' => $request->headers(),
                    'query'   => $request->query()->toArray(),
                ]);

                $decoded = json_decode($render, true);

                $p = 0;
                foreach ($decoded['data'] as $project) {
                    if (isset($project['content']['cover'])) {
                        $i = 0;
                        shuffle($project['content']['cover']);

                        $img = $project['content']['cover'][0];

                        $img['large'] = image($img['id'])->resize(1200, 1200, 90)->url();
                        $img['medium'] = image($img['id'])->resize(900, 900, 90)->url();
                        $img['small'] = image($img['id'])->resize(600, 600, 90)->url();
                        $img['orientation'] = image($img['id'])->orientation();
                        $img['width'] = image($img['id'])->width();
                        $img['ratio'] = image($img['id'])->ratio();

                        $project['content']['cover'][0] = $img;
                    };
                    $decoded['data'][$p] = $project;
                    $p++;
                };

                // Kirbytags
                function kt($array) {
                    foreach ($array as $key => $value) {
                        if (is_array($value)) {
                            $array[$key] = kt($value);
                        } else {
                            $array[$key] = kirbytags($value);
                        }
                    }
                    return $array;
                }

                $decoded = kt($decoded);
                $encoded = json_encode($decoded, true);

                return $encoded;
            }
        ]
    ],
    'hooks' => [
        'file.create:after' => function ($file) {
            $file['large'] = $file->resize(1200, 1200, 90)->url();
            $file['medium'] = $file->resize(900, 900, 90)->url();
            $file['small'] = $file->resize(600, 600, 90)->url();
        },
        'file.update:after' => function ($file) {
            $file['large'] = $file->resize(1200, 1200, 90)->url();
            $file['medium'] = $file->resize(900, 900, 90)->url();
            $file['small'] = $file->resize(600, 600, 90)->url();
        }
    ]
];
