# coachtech 勤怠管理アプリ（coachtech_Attendance-management）

## 環境構築

**Docker ビルド**

1. `git@github.com:yutaka58/Attendance-management.git`
2. `cd coachtech_Attendance-management`
3. DockerDesktop アプリを立ち上げる
4. `docker-compose up -d --build`

**Laravel 環境構築**

1. `docker-compose exec php bash`
2. `composer install`

3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成

```bash
mv .env.example
または
cp .env.example .env
```

4. .envに以下の環境変数を追加

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. Windows環境の場合は、「全てのファイル」と「.env」ファイルの権限変更。
   `sudo chmod -R 777 src/*`、 `sudo chmod -R 777 src/.env`

6. アプリケーションキーの作成

```bash
php artisan key:generate
```

7. マイグレーションの実行

```bash
php artisan migrate
```

8. シーディングの実行

```bash
php artisan db:seed
```

## 使用技術(実行環境)

- PHP8.1.33
- Laravel8.83.29
- MySQL8.0.26

## ER 図

![alt text](image.png)

## URL

- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
- MailHog (メール確認): http://localhost:8025/

## 備考

# Attendance-management
