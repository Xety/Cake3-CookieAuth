# Cake3 CookieAuth
A simple Cake3 plugin to authenticate users with Cookies. This plugin is based on the awesome plugin [FriendsOfCake/Authenticate](https://github.com/FriendsOfCake/Authenticate/tree/cake3) but with a different setup.

[![Build Status](https://img.shields.io/travis/Xety/Cake3-CookieAuth.svg?style=flat-square)](https://travis-ci.org/Xety/Cake3-CookieAuth)
[![Coverage Status](https://img.shields.io/coveralls/Xety/Cake3-CookieAuth/master.svg?style=flat-square)](https://coveralls.io/r/xety/Cake3-CookieAuth)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/Xety/Cake3-CookieAuth.svg?style=flat-square)](https://scrutinizer-ci.com/g/Xety/Cake3-CookieAuth)
[![Latest Stable Version](https://img.shields.io/packagist/v/Xety/Cake3-CookieAuth.svg?style=flat-square)](https://packagist.org/packages/xety/cake3-cookieauth)
[![Total Downloads](https://img.shields.io/packagist/dt/xety/cake3-cookieauth.svg?style=flat-square)](https://packagist.org/packages/xety/cake3-cookieauth)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://packagist.org/packages/xety/cake3-cookieauth)

## Requirements
* CakePHP 3.X

## Installation
Run : `composer require xety/cake3-cookieauth:1.*`
Or add it in your `composer.json`:
``` php
"require": {
    "xety/cake3-cookieauth": "1.*"
},
```

## Configuration
``` php
'Xety/Cake3CookieAuth.Cookie' => [
    'cookie' => [
        'name' => 'CookieAuth'
    ]
]
```
All others configuration option can be found on the official [CakePHP documentation](http://book.cakephp.org/3.0/en/controllers/components/authentication.html#configuring-authentication-handlers).

## Usage
In your `config/bootstrap.php` add :
``` php
Plugin::load('Xety/Cake3CookieAuth');
```

In your `AppController` :
``` php
public $components = [
    'Cookie',
    'Auth' => [
        'authenticate' => [
            'Form',
            'Xety/Cake3CookieAuth.Cookie'
        ]
    ]

];
```

In your `AppController`, in the `beforeFilter` action :
``` php
public function beforeFilter(Event $event) {
    //Automaticaly Login.
    if (!$this->Auth->user() && $this->Cookie->read('CookieAuth')) {

        $user = $this->Auth->identify();
        if ($user) {
            $this->Auth->setUser($user);
        } else {
            $this->Cookie->delete('CookieAuth');
        }
    }
}

//If you want to update some fields, like the last_login_date, or last_login_ip, just do :
public function beforeFilter(Event $event) {
    //Automaticaly Login.
    if (!$this->Auth->user() && $this->Cookie->read('CookieAuth')) {
        $this->loadModel('Users');

        $user = $this->Auth->identify();
        if ($user) {
            $this->Auth->setUser($user);

            $user = $this->Users->newEntity($user);
            $user->isNew(false);

            //Last login date
            $user->last_login = new Time();
            //Last login IP
            $user->last_login_ip = $this->request->clientIp();
            //etc...

            $this->Users->save($user);
        } else {
            $this->Cookie->delete('CookieAuth');
        }
    }
}
```

In your `login` action, after `$this->Auth->setUser($user);` :
``` php
//It will write Cookie without RememberMe checkbox
$this->Cookie->configKey('CookieAuth', [
    'expires' => '+1 year',
    'httpOnly' => true
]);
$this->Cookie->write('CookieAuth', [
    'username' => $this->request->data('username'),
    'password' => $this->request->data('password')
]);


//If you want use a RememberMe checkbox in your form :
//In your view
echo $this->Form->checkbox('remember_me');

//In the login action :
if($this->request->data('remember_me')) {
    $this->Cookie->configKey('CookieAuth', [
        'expires' => '+1 year',
        'httpOnly' => true
    ]);
    $this->Cookie->write('CookieAuth', [
        'username' => $this->request->data('username'),
        'password' => $this->request->data('password')
    ]);
}
```

## Contribute
[Follow this guide to contribute](https://github.com/Xety/Cake3-CookieAuth/blob/master/CONTRIBUTING.md)
