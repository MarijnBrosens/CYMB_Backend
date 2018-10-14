<?php

return [
    'debug'  => true,
    'routes' => [
        [
            'pattern' => 'rest/(:all)',
            'method'  => 'GET',
            'env'     => 'api',
            'action'  => function ($path = null) {
                $origin = $_SERVER['HTTP_ORIGIN'];
                $allowed_domains = [
                    'http://localhost:8080',
                    'https://constantinmirbach.com',
                    'https://www.constantinmirbach.com'
                ];
                
                if (in_array($origin, $allowed_domains)) {
                    header("Access-Control-Allow-Origin: " . $origin);
                }
                
                // header("Access-Control-Allow-Origin: *");
                                
                $kirby = new Kirby();
                $kirby->impersonate('api@connymirbach.de');

                if ($kirby->option('api') === false) {
                    return null;
                }

                $request = $kirby->request();
                                
                return $kirby->api()->render($path, $this->method(), [
                    'body'    => $request->body()->toArray(),
                    'files'   => $request->files()->toArray(),
                    'headers' => $request->headers(),
                    'query'   => $request->query()->toArray(),
                ]);
            }
        ]
    ]
];
