<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get()->keyBy('email');
        $books = Book::query()->get()->keyBy('isbn');

        $reviews = [

            // 吾輩は猫である
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784101010014',
                'rating' => 5,
                'comment' => '猫の視点が新鮮で、今読んでも面白い名作でした。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784101010014',
                'rating' => 4,
                'comment' => '少し古い表現もありますが、味わい深い作品です。',
            ],
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784101010014',
                'rating' => 5,
                'comment' => 'ユーモアと風刺が絶妙でした。',
            ],

            // 人を動かす
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784422100524',
                'rating' => 5,
                'comment' => '人間関係の基本が詰まった一冊でした。',
            ],
            [
                'user' => 'sato@example.com',
                'isbn' => '9784422100524',
                'rating' => 4,
                'comment' => '仕事にも私生活にも役立つ内容です。',
            ],
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784422100524',
                'rating' => 4,
                'comment' => '何度も読みたくなる本です。',
            ],

            // リーダブルコード
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784873115658',
                'rating' => 5,
                'comment' => 'エンジニア必読だと思います。',
            ],
            [
                'user' => 'takahashi@example.com',
                'isbn' => '9784873115658',
                'rating' => 5,
                'comment' => 'コードを書く意識が変わりました。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784873115658',
                'rating' => 4,
                'comment' => 'サンプルも多く理解しやすかったです。',
            ],

            // 7つの習慣
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784863940246',
                'rating' => 5,
                'comment' => '人生や仕事の考え方が変わる一冊でした。何度も読み返したいです。',
            ],
            [
                'user' => 'sato@example.com',
                'isbn' => '9784863940246',
                'rating' => 4,
                'comment' => '実践するには時間がかかりますが、とても参考になりました。',
            ],
            [
                'user' => 'takahashi@example.com',
                'isbn' => '9784863940246',
                'rating' => 5,
                'comment' => 'リーダーシップを学ぶ上で非常に役立つ内容でした。',
            ],

            // 坊っちゃん
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784101010021',
                'rating' => 5,
                'comment' => '主人公のまっすぐな性格に引き込まれました。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784101010021',
                'rating' => 4,
                'comment' => 'テンポよく読める夏目漱石の名作です。',
            ],
            [
                'user' => 'sato@example.com',
                'isbn' => '9784101010021',
                'rating' => 4,
                'comment' => '登場人物のやり取りが面白く、最後まで楽しめました。',
            ],

            // サピエンス全史
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784309226712',
                'rating' => 5,
                'comment' => '人類の歴史を新しい視点で学ぶことができました。',
            ],
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784309226712',
                'rating' => 5,
                'comment' => '知的好奇心を刺激される素晴らしい内容でした。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784309226712',
                'rating' => 3,
                'comment' => '文量が多いものの引き込まれる内容でした。',
            ],

            // Clean Code
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784048930598',
                'rating' => 5,
                'comment' => 'コードを書く意識が大きく変わりました。',
            ],
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784048930598',
                'rating' => 5,
                'comment' => '実務で何度も読み返したい一冊です。',
            ],

            // 嫌われる勇気
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784478025819',
                'rating' => 5,
                'comment' => '考え方が前向きになれる内容でした。',
            ],
            [
                'user' => 'takahashi@example.com',
                'isbn' => '9784478025819',
                'rating' => 4,
                'comment' => '対話形式で読みやすく、学びが多かったです。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784478025819',
                'rating' => 3,
                'comment' => '自分を見つめ直す切っ掛けになりました。',
            ],

            // 火花
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784163902302',
                'rating' => 4,
                'comment' => '芸人の世界がリアルに描かれていて面白かったです。',
            ],
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784163902302',
                'rating' => 3,
                'comment' => '独特の雰囲気でしたが、印象に残る作品でした。',
            ],
            [
                'user' => 'sato@example.com',
                'isbn' => '9784163902302',
                'rating' => 5,
                'comment' => '登場人物の心情描写が素晴らしかったです。',
            ],

            // FACTFULNESS
            [
                'user' => 'yamada@example.com',
                'isbn' => '9784822289607',
                'rating' => 5,
                'comment' => '思い込みを見直すきっかけになりました。',
            ],
            [
                'user' => 'suzuki@example.com',
                'isbn' => '9784822289607',
                'rating' => 4,
                'comment' => 'データに基づく考え方の重要性を学べました。',
            ],
            [
                'user' => 'takahashi@example.com',
                'isbn' => '9784822289607',
                'rating' => 5,
                'comment' => '世界を見る視点が変わる良書だと思います。',
            ],

            // コンテナ物語
            [
                'user' => 'tanaka@example.com',
                'isbn' => '9784822251468',
                'rating' => 4,
                'comment' => '物流の歴史を興味深く学ぶことができました。',
            ],
            [
                'user' => 'sato@example.com',
                'isbn' => '9784822251468',
                'rating' => 5,
                'comment' => '普段意識しないコンテナ輸送の重要性がよく分かりました。',
            ],
            [
                'user' => 'takahashi@example.com',
                'isbn' => '9784822251468',
                'rating' => 4,
                'comment' => '経済や物流に興味がある人におすすめしたい一冊です。',
            ],
        ];

        foreach ($reviews as $review) {

            Review::create([
                'book_id' => $books[$review['isbn']]->id,
                'user_id' => $users[$review['user']]->id,
                'rating' => $review['rating'],
                'comment' => $review['comment'],
            ]);
        }
    }
}
