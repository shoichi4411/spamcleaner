# Spam Cleaner
*スパムメール＆ウィルスメールフィルターです。*
## 概要
レンタルサーバーなどで使用しているメールにスパムフィルター機能を追加します。
## 必要なもの
・メールアドレス（.mailfilterなど、メールの転送などができるもの）
・PHPが動く環境
・Gmail１つ
## 処理の流れ
・メールサーバーにメールが受信
・.mailfilterでGmailにCCでメール送信
・.mailfilterでPHPの処理を実行
・PHPの処理にて、先述のGmailの中身をチェック
・Gmailにメールがなければ（ごみ箱に入っていれば）スパムと断定してメール破棄
・Gmailにメールがあれば、ヘッダー情報に以下の行を追加
*X-SpamCleaner-Version: SpamCleaner 0.0.1 (2013-01-09)*
*X-SpamCleaner-Status: Clean*
・おしまい
## 使用方法
・ダウンロードしたファイルをFTPなどでメールを使用しているサーバーにアップする。
・その際、ブラウザからアクセスできない場所にアップするのをオススメします。
・次にメールボックス以下に.mailfilterファイルを作成（中身は以下を参照）

cc "hoge@gmail.com"
xfilter "/usr/local/bin/php -q /home/hoge/sc/clean.php"
if (/^X-SpamCleaner-Status: Bad/:h)
{
	to /dev/null
}

※1行目のhoge@gmail.comの部分は、ご自分のGmailアドレスを入力してください。
※2行目の/usr/local/bin/phpの部分はレンタルサーバーによって変わります。
※2行目の/home/hoge/sc/clean.phpの部分はファイルをアップした場所を書いてください。
※3行目以降は、スパム判定した際の処理です。メールを削除しない場合（ヘッダー情報にはスパム判定が書かれています）は3行目以降を削除してください。
