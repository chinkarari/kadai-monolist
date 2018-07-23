
学習システム
キャリア
3
Lesson13
モノリスト
1. 学習の目標
Twitter クローンまでに学んだ内容さえわかってしまえば、多くの本格的な Web アプリケーションを作成することは可能でしょう。

今回はそのもう一歩先を行くために、外部サービスが提供している Web API を利用する方法を学びます。ここでは、楽天 API を例に、楽天市場の商品を共有するモノリストというアプリケーションを作成します。

2. 今回作成するWebアプリケーション
今回作成する Web アプリケーションは、楽天 API を利用して、楽天の商品を検索してその商品を共有するアプリケーションになります。名前はモノリストです。

2.1 デモサイト
下記に完成したモノリストを公開しています。ユーザ登録（Signup）して、アイテムを追加してみて、これから作成するアプリケーションのイメージを持っておいてください。（ただし、皆さんが作成したユーザやメッセージは予告無く削除されることがあります。）

モノリスト デモサイト
2.2 機能一覧
ユーザ登録／ログイン認証機能
楽天市場の商品検索
商品を Want, Have する
Want, Have された商品の一覧表示
Want, Have のランキング
2.3 使用画像
今回は下記の画像を使用します。

ロゴ画像
カバー画像
favion 画像
これらの画像は既に用意しているので、下記でダウンロードしてください。Zip ファイルなので解凍してください。

モノリストでの使用画像
favicon
favicon（ファビコン）は、お気に入り(fav)に追加したときに表示されるアイコン画像(icon)です。ブラウザのタブにも表示されます。



/ のルーティングに favicon.ico というファイル名で置いておくとブラウザにより自動認識されます。

3. プロジェクトの開始
いつも通りプロジェクトを作成していきます。

3.1 プロジェクトの作成
composer で Laravel プロジェクトを作成します。プロジェクト名は monolist です。プロジェクト作成時はカレントディレクトリ（ pwd で表示される現在フォルダ）には気をつけてください。

$ cd ~/environment/
$ composer create-project laravel/laravel ./monolist "5.5.*" --prefer-dist
3.2 動作確認
起動して welcome ページが表示されるか確認しておいてください。 welcome.blade.php が読み込まれ「Laravel」が表示されていれば大丈夫です。

3.3 Git
Git でバージョン管理を開始しておきましょう。カレントディレクトリ（現在フォルダ）にも気をつけてください。

$ git init

$ git add .

$ git commit -m 'init'
4. データベースと接続
4.1 .env の修正
.env を修正して、データベース設定に関する環境変数を変更します。

.env

DB_DATABASE=monolist
DB_USERNAME=root
DB_PASSWORD=
4.2 データベースの作成
DB_DATABASE=monolist と環境変数を設定したので、 monolist データベースを作成します。

$ mysql -u root

mysql> CREATE DATABASE monolist;

mysql> exit
4.3 tinker で接続確認
tinker を起動し、データベースの接続を確認します。 DB::connection() でエラーが出なければ問題なく接続できています。

$ php artisan tinker

>>> DB::connection()
4.4 タイムゾーンと言語設定
タイムゾーンの設定
タイムゾーンの設定をしておけば、 Model からレコードを保存したときなどで時間情報 (created_at 等)が設定したタイムゾーンで保存されます。

config/app.php timezone抜粋

    timezone => 'Asia/Tokyo',
4.5 Git
$ git status

$ git diff

$ git add .

$ git commit -m 'set timezone'
5. トップページ
ログイン前のトップページには Model 操作はありませんので View のみを作成していきます。共通で利用するレイアウトやエラーメッセージなども実装しておきましょう。トップページは welcome.blade.php をそのまま修正していきましょう。

なお、動作確認は各自のタイミングで行ってください。

5.1 Model
モデルはありません。

5.2 Router
/ にアクセスしたとき、 WelcomeController@index に飛ぶようにします。

routes/web.php

Route::get('/', 'WelcomeController@index');
5.3 WelcomeController@index
Controller
Router の通り、トップページのアクセスには、 WelcomeController@index で対応します。

app/Http/Controllers/WelcomeController.php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Item;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome');
    }
}
この段階では、 resources/views/welcome.blade.php を表示するだけです。後ほど充実させます。

View
共通レイアウト
resources/views/layouts/app.blade.php

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Monolist</title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <link rel="stylesheet" href="{{ secure_asset('css/style.css') }}">
    </head>
    <body>
        @include('commons.navbar')

        @yield('cover')

        <div class="container">
            @include('commons.error_messages')
            @yield('content')
        </div>

        @include('commons.footer')
    </body>
</html>
今までと違うところを説明します。

<link rel="stylesheet" href="{{ secure_asset('css/style.css') }}"> は、 {{ secure_asset('css/style.css') }} のコードにより、自身のドメイン直下(xxx.com/など)を指し示すことになります。そのため、実際にブラウザから読み込まれたときに、例えばドメインがCloud9だった場合には、 <link rel="stylesheet" href="http://techacademy-php-username.c9users.io:8080/css/style.css"> のようになります。つまり secure_asset('...') により正しく CSS の URL を指定することができます。

また、ドメイン直下に css/style.css を設置するには、public フォルダの直下 public/css/style.css に設置すれば良いです。 public/ 内のものは secure_asset('...') により指し示すことができます。

@yield('cover'), @yield('content') が2つあり、 cover のほうは .container の外にあります。これは呼び出す側の View (welcome.blade.php 等) で、 @section('cover') や @section('content') が定義され、その内容がその位置に埋め込まれるというだけです。後ほど、 welcome.blade.php を編集するところで理解できると思います。

エラーメッセージ
resources/views/commons/error_messages.blade.php

@if (count($errors) > 0)
    @foreach ($errors->all() as $error)
        <div class="alert alert-warning">{{ $error }}</div>
    @endforeach
@endif
ナビバー
resources/views/commons/navbar.blade.php

<header>
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-left" href="/"><img src="{{ secure_asset("images/logo.png") }}" alt="Monolist"></a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#">新規登録</a></li>
                    <li><a href="#">ログイン</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
ここでも "{{ secure_asset("images/logo.png") }}" のようにしているので、 public/ 直下に public/images/logo.png を設置することになります。これは後に行います。

フッター
resources/views/commons/footer.blade.php

<footer>
    <div class="text-center text-muted">© 2016 MONOLIST.</div>
</footer>
welcome
welcome.blade.php に予め書かれていたものは不要なので全て削除して、下記の通りにしてください。

resources/views/welcome.blade.php

@extends('layouts.app')

@section('cover')
    <div class="cover">
        <div class="cover-inner">
            <div class="cover-contents">
                <h1>素敵なモノと出会う場所</h1>
                <a href="" class="btn btn-success btn-lg">モノリストを始める</a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    テスト
@endsection
ここで、 @section('cover') の内容は resouces/views/layouts/app.blade.php の @yield('cover') に埋め込まれ、@section('content') の内容（テストのみ）は resouces/views/layouts/app.blade.php の @yield('content') に埋め込まれます。埋め込まれた後に、 class="container" の中にあるか外にあるかの違いです。

CSS ファイル
今回は、少しだけデザインにも凝ってみましょう。resouces/views/layouts/app.blade.php で <link rel="stylesheet" href="{{ secure_asset('css/style.css') }}"> とコーディングしたので、CSSファイルを public/css/style.css に配置してください。

public/css/style.css

@charset "utf-8";

/* body */
body {
    background: #f2f2f2;
}
footer {
    margin-top: 40px;
}

/* navbar */
.navbar {
    background-color: #fff;
}
.navbar-header img {
    margin-top: 5px;
    height: 40px;
}

/* cover */
.cover {
    margin-top: -20px;
    margin-bottom: 20px;
    width: 100%;
    height: 300px;
    background: url("/images/cover-bg.jpg") center center no-repeat;
    background-size: cover;
}
.cover .cover-inner {
    height: 100%;
    margin: auto;
    display: table;
}
.cover .cover-inner .cover-contents {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    position: relative;
}
.cover .cover-inner .cover-contents h1 {
    margin: 0 0 20px 0;
    color: #fff;
    font-weight: bold;
    letter-spacing: 0.15em;
}
.cover .btn-success {
    background-color: #ed486f;
    border-color: #ed486f;
}
画像の配置
モノリストでの使用画像
上記でダウンロードした3つの画像をそれぞれ配置します。

ロゴ画像 ( logo.png ) は、 public/images/logo.png へ
カバー画像 ( cover-bg.jpg ) は、 public/images/cover-bg.jpg へ
favion 画像 ( favicon.ico ) は、 public/favicon.ico へ
これでトップページの表示は完成です。動作確認もしておいてください。

5.4 Git
$ git status

$ git diff

$ git add .

$ git commit -m 'top page'
6. ユーザ登録機能
次に、ユーザ登録機能を作成していきます。Twitter クローンと同じです。

6.1 Model
テーブル設計前の初期設定
app/Providers/AppServiceProvider.php

    public function boot()
    {
        //
    }
上記のbootメソッドの中身を以下の内容を追記しましょう。

    public function boot()
    {
        \Schema::defaultStringLength(191);
    }
これでテーブル設計のための初期設定は完了です。

マイグレーション
Laravel がマイグレーションファイルを用意しているので、マイグレーションを実行します。

$ php artisan migrate
User モデルの確認
app/User.php を確認しておいてください。

6.2 Router
routes/web.php

// ユーザ登録
Route::get('signup', 'Auth\RegisterController@showRegistrationForm')->name('signup.get');
Route::post('signup', 'Auth\RegisterController@register')->name('signup.post');
6.3 RegisterController@showRegistrationForm, register
Controller
app/Http/Controllers/Auth/RegisterController.php

    protected $redirectTo = '/';
View
laravelcollective のインストール
Laravel Collective
composer.json の “require”

    "require": {
        "php": ">=7.0.0",
        "fideloper/proxy": "~3.3",
        "laravel/framework": "5.5.*",
        "laravel/tinker": "~1.0",
        "laravelcollective/html": "5.5.*"
    },
アップデートします。

$ composer update
登録フォームの設置
resources/views/auth/register.blade.php

@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-offset-3 col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">会員登録</div>
            <div class="panel-body">
                {!! Form::open(['route' => 'signup.post']) !!}
                    <div class="form-group">
                        {!! Form::label('name', 'お名前') !!}
                        {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('email', 'メールアドレス') !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password', 'パスワード') !!}
                        {!! Form::password('password', ['class' => 'form-control']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'パスワード（確認）') !!}
                        {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                    </div>

                    <div class="text-right">
                        {!! Form::submit('登録する', ['class' => 'btn btn-success']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection