<?php

/* In this example, the user database is simply a directory of json files
  named by their username (urlencoded so there are no weird characters
  in the file names). For simplicity, it's in the HTML tree so someone
  could look at it - you really, really don't want to do this for a
  live system */
define('USER_DATABASE', dirname(dirname(__DIR__)).'/.users');
if (! file_exists(USER_DATABASE)) {
    if (! @mkdir(USER_DATABASE)) {
        error_log(sprintf('Cannot create user database directory - is the html directory writable by the web server? If not: "mkdir %s; chmod 777 %s"', USER_DATABASE, USER_DATABASE));
        die(sprintf("cannot create %s - see error log", USER_DATABASE));
    }
}
session_start();

function oops($s){
    http_response_code(400);
    echo "{$s}\n";
    exit;
}

function userpath($username){
    $username = str_replace('.', '%2E', $username);
    return sprintf('%s/%s.json', USER_DATABASE, urlencode($username));
}

function getuser($username){
    $user = @file_get_contents(userpath($username));
    if (empty($user)) { oops('user not found'); }
    $user = json_decode($user);
    if (empty($user)) { oops('user not json decoded'); }
    return $user;
}

/* A post is an ajax request, otherwise display the page */
if (! empty($_POST)) {

    try {
        $webauthn = new \Login\WebAuth($_SERVER['HTTP_HOST']);

        switch(TRUE){

            case isset($_POST['registerusername']):
                /* initiate the registration */
                $username = $_POST['registerusername'];
                $crossplatform = !1;
                $userid = md5(time() . '-'. rand(1,1000000000));

                if (file_exists(userpath($username))) {
                    oops("user '{$username}' already exists");
                }

                /* Create a new user in the database. In principle, you can store more
                   than one key in the user's webauthnkeys,
                   but you'd probably do that from a user profile page rather than initial
                   registration. The procedure is the same, just don't cancel existing
                   keys like this.*/
                file_put_contents(userpath($username), json_encode(['name'=> $username,
                    'id'=> $userid,
                    'webauthnkeys' => $webauthn->cancel()]));
                $_SESSION['username'] = $username;
                $data['data'] = ['challenge' => $webauthn->prepareChallengeForRegistration($username, $userid, $crossplatform)];
                break;

            case isset($_POST['register']):
                /* complete the registration */
                if (empty($_SESSION['username'])) { oops('username not set'); }
                $user = getuser($_SESSION['username']);

                /* The heart of the matter */
                $user->webauthnkeys = $webauthn->register($_POST['register'], $user->webauthnkeys);

                /* Save the result to enable a challenge to be raised agains this
                   newly created key in order to log in */
                file_put_contents(userpath($user->name), json_encode($user));
                $data['data'] = 'ok';

                break;

            case isset($_POST['loginusername']):
                /* initiate the login */
                $username = $_POST['loginusername'];
                $user = getuser($username);
                $_SESSION['loginname'] = $user->name;

                /* note: that will emit an error if username does not exist. That's not
                   good practice for a live system, as you don't want to have a way for
                   people to interrogate your user database for existence */

                $data['data']['challenge'] = $webauthn->prepareForLogin($user->webauthnkeys);
                break;

            case isset($_POST['login']):
                /* authenticate the login */
                if (empty($_SESSION['loginname'])) { oops('username not set'); }
                $user = getuser($_SESSION['loginname']);

                if (! $webauthn->authenticate($_POST['login'], $user->webauthnkeys)) {
                    http_response_code(401);
                    echo 'failed to authenticate with that key';
                    exit;
                }
                $data['data'] = 'ok';

                break;

            default:
                http_response_code(400);
                echo "unrecognized POST\n";
                break;
        }

    } catch(Exception $ex) {
        oops($ex->getMessage());
    }
}