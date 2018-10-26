<?php

return [
    'debug'  => true,
    'routes' => [
        [
            'pattern' => 'rest/(:all)',
            'method'  => 'GET',
            'env'     => 'api',
            'action'  => function ($path = null) {


                $kirby = new Kirby();
                $kirby->impersonate('kirby');

                if ($kirby->option('api') === false) {
                    return null;
                }

                $origin = $_SERVER['HTTP_ORIGIN'];
                $allowed_domains = [
                    'http://localhost:8080',
                    'https://constantinmirbach.com',
                    'https://www.constantinmirbach.com'
                ];

                $request = $kirby->request();
                $headers = $request->headers();

                if (in_array($origin, $allowed_domains)) {
                    $headers['Access-Control-Allow-Origin'] = $origin;
                }

                $render = $kirby->api()->render($path, $this->method(), [
                    'body'    => $request->body()->toArray(),
                    'files'   => $request->files()->toArray(),
                    'headers' => $headers,
                    'query'   => $request->query()->toArray(),
                ]);

                $decoded = json_decode($render, true);

                function kt($array)
                {
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
                $encoded = json_encode($decoded);

                return $decoded;
            }
        ]
    ]
];
