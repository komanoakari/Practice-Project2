````markdown
# 模擬案件\_勤怠管理アプリ

従業員の勤怠打刻から管理者による承認まで、
勤怠管理業務を想定して作成したアプリです。
出退勤の記録、修正申請、CSV 出力などの基本機能を実装しています。

## 環境構築

### Docker ビルド

1. リポジトリをクローン

```bash
   git clone git@github.com:komanoakari/Practice-Project2.git
   cd Practice-Project2
```

2. DockerDesktop アプリを立ち上げる

3. Docker コンテナをビルド・起動

```bash
   docker compose up -d --build`
```git

### Laravel 環境構築

> `.env` は `./src/` ディレクトリ直下に置きます。

1. PHP コンテナに入る

```bash
   docker compose exec php bash
   cd src
```

2. Fortify をインストール

```bash
   composer install
```

3. 「.env.example」を「.env」にコピー

```bash
   cp .env.example .env
```

4. .env に以下の環境変数を追加

```text
   APP_URL=http://localhost:8018

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass

   # MailHog
   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS=noreply@example.test
   MAIL_FROM_NAME="${APP_NAME}"
```

5. アプリケーションキーの作成

```bash
   php artisan key:generate
   php artisan config:clear
```

6. マイグレーション & 初期データ投入

```bash
   php artisan migrate --seed
```

## 使用技術(実行環境)

- PHP: 8.1.33
- Laravel: 8.83.29
- Nginx: 1.21.1
- MySQL: 8.0.26
- phpMyAdmin
- MailHog
- Docker Desktop（Compose v2）
- Git

## URL

- 開発環境: http://localhost:8018/
- phpMyAdmin: http://localhost:8019/（Server は `mysql`）
- MailHog（メール受信 BOX）: http://localhost:8025/（SMTP は `mailhog:1025`）

## phpMyAdmin ログイン情報

- URL: http://localhost:8019/
- Server: `mysql`
- Username: `laravel_user`
- Password: `laravel_pass`

## PHPUnit テスト

1. Docker コンテナを起動

```bash
docker compose up -d
```

2. テスト環境の.env 作成+鍵発行

```bash
cp src/.env.testing.example src/.env.testing
docker compose exec php bash -lc 'cd src && php artisan key:generate --env=testing'
```

3. テスト用 DB を作成

```bash
docker compose exec mysql mysql -uroot -proot
```

-- 以下、MySQL プロンプト内で実行

```sql
CREATE DATABASE IF NOT EXISTS laravel_db_testing
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `laravel_db_testing`.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
EXIT;
```

4. テスト実行

```bash
docker compose exec php bash -lc 'cd src && php artisan test'
```

## ログイン用アカウント

マイグレーション時に、以下のアカウントが作成されます。

### 一般ユーザー

- メール：`test@example.com`
- パスワード：`password`

### 管理ユーザー

- メール：`admin@gmail.com`
- パスワード：`password`

## ER 図

![ER図](er.png)
````
