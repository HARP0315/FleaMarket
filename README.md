# ■coachtechフリマ（FleaMarket）

ある企業が開発した独自のフリマアプリ。
アイテムの出品と購入を行う。

## ■使用技術

### バックエンド

- PHP: 8.3.0
- フレームワーク: Laravel 10.48.29

### データベース

- MySQL: 8.4.0

### フロントエンド

- JavaScript
- CSS
- View: Blade
- パッケージ管理: npm（未使用）

### テスト

- ユニットテスト (Unit/Feature): PHPUnit
- E2Eテスト (Browser): Laravel Dusk
- ブラウザ自動化 (Driver): Selenium / ChromeDriver
- コンテナ作成

### ファイルストレージ

- Laravel Filesystem (local driver)

### 開発環境

- コンテナ仮想化 : Docker
- メールテスト: MailHog

### 外部サービス連携

- 決済: Stripe
- 通知: Webhook

## ■環境構築

### 1. 前提条件

- Git
- Docker Desktop

### 2. Dockerビルド

1. このリポジトリをクローンします。

    ```bash
    git clone https://github.com/HARP0315/FleaMarket.git
    ```

2. Docker Desktopアプリを立ち上げます。

3. プロジェクトのルートディレクトリに移動し、Dockerコンテナをビルドして起動します。

    ```bash
    docker-compose up -d --build
    ```

※M1/M2 Mac ユーザーの方
no matching manifest for linux/arm64/v8 in the manifest list entries というエラーが表示された場合は、`docker-compose.yml`ファイルのmysqlサービスにplatform: linux/x86_64を追加してください。

```mysql
  platform: linux/x86_64 # この行を追加
  image: mysql:8.0.26
  # ...
```

### 3. Laravel環境構築

1. PHPコンテナの中に入ります。

    ```bash
    docker-compose exec php bash
    ```

2. **ここから下のコマンドはPHPコンテナ内で実行します。**

3. Composerパッケージをインストールします。

    ```bash
    composer install
    ```

4. `.env.example`ファイルをコピーして`.env`ファイルを作成します。

    ```bash
    cp .env.example .env
    ```

5. .envに以下の環境変数を追加します。

    ```env
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel_db
    DB_USERNAME=laravel_user
    DB_PASSWORD=laravel_pass
    ```

6. アプリケーションキーを生成します。

    ```bash
    php artisan key:generate
    ```

7. データベースのマイグレーションを実行します。（テーブルが作成されます）

    ```bash
    php artisan migrate
    ```

8. 初期データを作成します。

    ```bash
    php artisan db:seed
    ```

9. 画像ファイルを公開ディレクトリから参照できるように、シンボリックリンクを作成します。

    ```bash
    php artisan storage:link
    ```

10. コンテナから抜けます。

    ```bash
    exit
    ```

### 4. アプリケーションへのアクセス

- Web: [http://localhost](http://localhost)

## ■各種ツールのセットアップと実行

### MailHog (メールテスト)

- **目的**: 開発環境で送信されるメールをキャッチし、実際のメールアドレスに送信せずに内容を確認します。
- **設定**: `.env`ファイルが以下のようになっていることを確認してください。

    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=mailhog
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS="hello@example.com"
    MAIL_FROM_NAME="${APP_NAME}"
    ```

- **アクセス**: [http://localhost:8025](http://localhost:8025)

### Stripe (決済) & Webhook

- **目的**: クレジットカード決済機能と、決済イベントを非同期で受信するWebhookをローカルでテストします。
- **設定**:

1. `.env`ファイルにStripeのAPIキーとWebhookシークレットキーを設定します。
STRIPE_WEBHOOK_SECRETは、後ほどStripe CLIにログインした際にstripe listenコマンドを打つと表示されます

    ```env
    STRIPE_KEY=pk_test_...
    STRIPE_SECRET=sk_test_...
    STRIPE_WEBHOOK_SECRET=whsec_...
    ```

2. 設定変更の反映

    ```bash
    php artisan optimize:clear
    ```

    ```bash
    exit
    ```

3. ローカルでWebhookをテストするために、ホストマシンにStripe CLIをインストールします。

【Stripe CLIのインストール手順】

- **macOS (Homebrew):**

    ```bash
    brew install stripe/stripe-cli/stripe
    ```

- **Windows (Scoop):**

    ```bash
    scoop install stripe
    ```

- **Linux (Debian/Ubuntu):**

    ```bash
    curl -sL [https://stripe.com/install.sh](https://stripe.com/install.sh) | sudo bash
    ```

- **実行**:

    1. Stripe CLIにログインします。

        ```bash
        stripe login
        ```

        ブラウザでの認証を求められるのでEnterを押し、ブラウザに出た画面にて認証をします。

    2. Webhookイベントを待ち受け、アプリケーションに転送します。
    **【重要】** Webhookに関連する機能（決済成功後の処理など）を動作させる際は、**必ず**別のターミナルで以下のコマンドを実行し、Stripeからのイベントを待ち受ける必要があります。このコマンドが実行されていないと、Webhookは機能しません。

        ```bash
        # ホストマシン（ローカルのターミナル）で実行
        stripe listen --forward-to http://localhost/stripe/webhook
        ```

### PHPUnit (ユニットテスト/フィーチャーテスト)

- **目的**: アプリケーションの内部ロジックをテストします。
- **設定**:

    1. `phpunit.xml`ファイルを開き、以下の行が含まれているか確認してください。

        ```xml
        <php>
            # ...
            <server name="APP_ENV" value="testing"/>
            <server name="DB_DATABASE" value="fleamarket_test"/>
            # ...
        </php>
        ```

    2. `.env`ファイルをコピーして`.env.testing`ファイルを作成します。

        ```bash
        cp .env .env.testing
        ```

    3. `.env.testing`ファイルの下記環境変数の編集および追加を行います。

        ```env
        （編集）
        APP_ENV=testing

        # ...
        （編集）
        DB_DATABASE=fleamarket_test
        # ...

        （追加）
        DB_USERNAME=root
        DB_PASSWORD=... ←docker-compose.ymlファイル記載のrootパスワードを追加
        ```

- **テスト実行前の準備 (初回のみ)**:

  - テスト用のデータベースを作成します。

    ```bash
    # 1. PHPコンテナに入る
    docker-compose exec mysql bash

    # 2. MySQLクライアントに接続する (パスワードを求められたら .env の DB_PASSWORD を入力)
    mysql -h mysql -u root -p

    # 3. MySQLプロンプトで、テスト用データベースを作成する
    CREATE DATABASE fleamarket_test;

    # 4. MySQLクライアントを終了
    exit;

    # 5. PHPコンテナを終了
    exit;
    ```

- **実行**: PHPコンテナ内で以下のコマンドを実行します。

    ```bash
    # コンテナに入る
    docker-compose exec php bash

    # テスト実行
    php artisan test
    ```

### Laravel Dusk & Selenium (E2Eテスト)

- **目的**: 実際のブラウザ（Google Chrome）を操作して、ユーザー視点のE2Eテストを実行します。SeleniumはDuskがブラウザを操作するために利用します。

- **設定**:

    1. Duskをインストールします。

        ```bash
        docker-compose exec php
        ```

        ```bash
        php artisan dusk:install
        ```

    2. PHPコンテナ内で以下のコマンドを実行します。

        ```bash
        cp .env .env.dusk.local
        ```

    3. `.env.dusk.local`に下記を記載します（全て消して下記だけでもいい）。

        ```env
        APP_ENV=testing
        APP_DEBUG=true
        APP_URL=http://nginx
        APP_KEY=base64:vvo/0YInTAucu0HEAtIirJuGKFYT2oP4o9665aNILyE=

        DB_CONNECTION=mysql
        DB_HOST=mysql
        DB_PORT=3306
        DB_DATABASE=fleamarket_test

        DB_USERNAME=root
        DB_PASSWORD=... ←docker-compose.ymlファイル記載のrootパスワードを追加

        DUSK_DRIVER_REMOTE_URL=http://selenium:4444/wd/hub
        ```

    4. 設定変更の反映

        ```bash
        php artisan optimize:clear
        ```

    5. マイグレーションを実行

        ```bash
        php artisan migrate:fresh --env=dusk.local
        ```

- **実行**:

    1. `docker-compose.yml`で`selenium`サービスが起動していることを確認してください。
    2. PHPコンテナ内で以下のコマンドを実行します。

        ```bash
        # コンテナに入る
        docker-compose exec php bash

        # Duskテスト実行
        php artisan dusk
        ```

## ■URL

- 開発環境：[http://localhost/](http://localhost/)
- phpMyAdmin:：[http://localhost:8080/](http://localhost:8080/)

## ■その他仕様

### 購入機能

- 商品購入ページにて、「購入する」ボタンを押下
- Stripe の支払いページに遷移
  - コンビニ支払、カード支払にてページ分岐
  - この時点で購入情報をDB仮登録（payment_statusは未入金）
- 購入成功した場合、トップページに遷移
- キャンセルした場合、商品詳細ページに遷移
  - 仮登録した購入情報は物理削除
- Webhookより'checkout.session.completed'が届く
  - 送付先情報をDB登録
  - 購入情報をDB本登録（payment_statusは未入金）
- Webhookより'payment_intent.succeeded'が届く
  - 購入情報を更新（payment_statusは入金済）
- Webhookより'payment_intent.canceled'、'payment_intent.payment_failed'が届く
  - 購入情報を論理削除（'is_deleted'のフラグが立つ）
