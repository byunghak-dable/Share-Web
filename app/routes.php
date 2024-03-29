<?php

// 메인 홈페이지 : 상단 네비게이션 바로 이동 하는 루트

$router->get('', 'PagesController@home');
$router->get('news', 'PagesController@news');
$router->get('brand', 'PagesController@brand');
$router->post('brand', 'PagesController@brand');
// 자유계시판 관련 루트
$router->get('board', 'BoardController@board');
$router->get('board-read', 'BoardController@boardRead');
$router->get('board-write', 'BoardController@boardWrite');
$router->post('board-write', 'BoardController@boardWrite');
$router->post('board-writeHandler', 'BoardController@boardWriteHandler');
$router->post('board-delete', 'BoardController@boardDeletePost');
$router->post('board-comment', 'BoardController@boardComment');
$router->post('board-delete-comment', 'BoardController@boardDeleteComment');
$router->post('board-reply', 'BoardController@boardReply');
$router->post('board-delete-reply', 'BoardController@boardDeleteReply');

$router->get('broadcast', 'PagesController@broadcast');
$router->post('broadcast', 'PagesController@broadcast');
// 로그인 루트
$router->get('login', 'UserController@login');
$router->post('login-startSession', 'UserController@login');
$router->get('logout', 'UserController@logout');
// 회원가입 루트
$router->get('register', 'UserController@register');
$router->post('registerHandler', 'UserController@register');

// 관리자 페이지
$router->get('adminPage', 'UserController@adminPage');

$router->post('cookie-handler', 'PagesController@cookieHandler');
