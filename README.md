# Freamarket

## 環境構築

### Docker ビルド

1. git clone git@github.com:starmine448357/freamarket.git
2. cd freamarket
3. docker compose up -d --build

---

## Laravel 環境構築

1. docker compose exec app bash
2. composer install
3. cp .env.example .env
4. .env を以下に修正：

 DB_CONNECTION=mysql  
 DB_HOST=mysql  
 DB_PORT=3306  
 DB_DATABASE=laravel_db  
 DB_USERNAME=laravel_user  
 DB_PASSWORD=laravel_pass  

5. php artisan key:generate  
6. php artisan migrate:fresh --seed  
7. php artisan storage:link  

---

## 初期データ（ダミーデータ）

### ユーザー（3名）
| 名前 | メール | 役割 |
|------|--------|------|
| 出品者A | sellerA@example.com | 出品者 |
| 出品者B | sellerB@example.com | 出品者 |
| 出品者C | userC@example.com| 出品なし |

※ パスワードは全員 `password`

---

## Stripeについて
コンビニ支払いとカード支払いのオプションがありますが、決済画面にてコンビニ支払いを選択しますと、レシートを印刷する画面に遷移します。そのため、カード支払いを成功させた場合に意図する画面遷移が行える想定です。<br>

また、StripeのAPIキーは以下のように設定をお願いいたします。
```
STRIPE_PUBLIC_KEY="パブリックキー"
STRIPE_SECRET_KEY="シークレットキー"
```

以下のリンクは公式ドキュメントです。<br>
https://docs.stripe.com/payments/checkout?locale=ja-JP

---

## メール認証について
このアプリでは MailHog を使用してメール認証（確認メール送信）の動作確認を行います。

Docker を起動すると MailHog も自動的に立ち上がります。
Docker 起動後、ブラウザで以下にアクセスしてください：

　http://localhost:8025/

ここに送信されたメールが一覧表示されます。

---

## 使用技術

- MySQL 8  
- PHP 8.2  
- Laravel 10  
- Docker  

---

## URL 一覧

- アプリ本体: http://localhost:8080/  
- phpMyAdmin: http://localhost:8082/  
- MailHog: http://localhost:8025/  

---

# テーブル仕様一覧（全テーブル）

---

## ■ users
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ |  | ○ |  |
| name | varchar(255) |  |  | ○ |  |
| email | varchar(255) |  | ○ | ○ |  |
| email_verified_at | timestamp |  |  |  |  |
| password | varchar(255) |  |  | ○ |  |
| postal_code | varchar(20) |  |  |  |  |
| address | varchar(255) |  |  |  |  |
| building | varchar(255) |  |  |  |  |
| profile_image_path | varchar(255) |  |  |  |  |
| remember_token | varchar(255) |  |  |  |  |
| created_at | timestamp |  |  | ○ |  |
| updated_at | timestamp |  |  | ○ |  |

---

## ■ categories
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| name | varchar(100) | | | ○ | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ■ items
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | | ○ | ○ users.id |
| title | varchar(255) | | | ○ | |
| brand | varchar(255) | | |  | |
| description | text | | |  | |
| price | int | | | ○ | |
| condition | enum(new, like_new, used) | | | ○ | |
| image_path | varchar(255) | | |  | |
| status | enum(selling, sold) | | | ○ | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ■ item_category
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| item_id | bigint | ○ | | ○ | ○ items.id |
| category_id | bigint | ○ | | ○ | ○ categories.id |

---

## ■ likes
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | | ○ | ○ users.id |
| item_id | bigint | | | ○ | ○ items.id |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |
| (user_id, item_id) | - | | ○ | ○ | |

---

## ■ comments
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | | ○ | ○ users.id |
| item_id | bigint | | | ○ | ○ items.id |
| content | varchar(255) | | | ○ | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ■ purchases
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| user_id | bigint | | | ○ | ○ users.id |
| buyer_id | bigint | | | ○ | ○ users.id |
| item_id | bigint | | | ○ | ○ items.id |
| payment_method | varchar(20) | | | ○ | |
| amount | int | | | ○ | |
| shipping_postal_code | varchar(20) | | | ○ | |
| shipping_address | varchar(255) | | | ○ | |
| shipping_building | varchar(255) | | |  | |
| status | varchar(20) | | | ○ | |
| paid_at | timestamp | | |  | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ■ transaction_messages
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| purchase_id | bigint | | | ○ | ○ purchases.id |
| user_id | bigint | | | ○ | ○ users.id |
| message | text | | |  | |
| image_path | varchar(255) | | |  | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ■ purchase_user_reads
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| purchase_id | bigint | | | ○ | ○ purchases.id |
| user_id | bigint | | | ○ | ○ users.id |
| last_read_at | timestamp | | |  | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |
| (purchase_id, user_id) | - | | ○ | ○ | |

---

## ■ reviews
| カラム名 | 型 | PK | UNIQUE | NOT NULL | FK |
|---------|----|----|--------|----------|----|
| id | bigint | ○ | | ○ | |
| purchase_id | bigint | | | ○ | ○ purchases.id |
| reviewer_id | bigint | | | ○ | ○ users.id |
| target_id | bigint | | | ○ | ○ users.id |
| rating | tinyint | | | ○ | |
| comment | text | | |  | |
| created_at | timestamp | | | ○ | |
| updated_at | timestamp | | | ○ | |

---

## ER 図

![ER図](src/public/images/ER.png)
