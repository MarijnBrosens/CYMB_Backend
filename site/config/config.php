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
    ]
];
