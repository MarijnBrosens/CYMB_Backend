<?php
return [
    'debug' => false,
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
                $kirby->impersonate('kirby');

                if ($kirby->option('api') === false) {
                    return null;
                }

                $request = $kirby->request();
                $headers = $request->headers();
                $csrf = csrf();
                $headers['X-CSRF'] = $csrf;
                
                $render = $kirby->api()->render($path, $this->method(), [
                    'body'    => $request->body()->toArray(),
                    'files'   => $request->files()->toArray(),
                    'headers' => $headers,
                    'query'   => $request->query()->toArray(),
                ]);

                $decoded = json_decode($render, true);

                // Image sizes
                $segments = explode('/', $path);
                $last = array_pop($segments);
                if ($last === "files") {
                    $n = 0;
                    foreach ($decoded['data'] as $img) {
                        $img = image($img['id']);
                        $decoded['data'][$n]['large'] = $img->resize(1200, 1200, 90)->url();
                        $decoded['data'][$n]['medium'] = $img->resize(900, 900, 90)->url();
                        $decoded['data'][$n]['small'] = $img->resize(600, 600, 90)->url();
                        $n++;
                    };
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
            $file->resize(1200, 1200, 90)->url();
            $file->resize(900, 900, 90)->url();
            $file->resize(600, 600, 90)->url();
        },
        'file.update:after' => function ($file) {
            $file->resize(1200, 1200, 90)->url();
            $file->resize(900, 900, 90)->url();
            $file->resize(600, 600, 90)->url();
        }
    ]
];
