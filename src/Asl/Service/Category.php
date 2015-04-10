<?php


namespace Asl\Service;

class Category
{

    /**
     * ブック名からカテゴリを導き出す
     *
     * @param  string $book_name
     * @return string
     **/
    public static function convert ($book_name)
    {
        if (substr($book_name, 0, 4) == 'WCLA') {
            $c = 'パーツマニュアル';
        } elseif (substr($book_name, 0, 4) == 'WCLC') {
            $c = 'パーツマニュアル';
        } elseif (substr($book_name, 0, 4) == 'WCLB') {
            $c = 'パーツマニュアル';
        } elseif (substr($book_name, 0, 5) == 'PMACS') {
            $c = 'パーツマニュアル';
        } elseif (substr($book_name, 0, 4) == 'WLST') {
            $c = 'サービステキスト';
        } elseif (substr($book_name, 0, 4) == 'WLSM') {
            $c = 'サービスマニュアル';
        } elseif (substr($book_name, 0, 5) == 'WLOPT') {
            $c = 'オプション取付要領書';
        } elseif (substr($book_name, 0, 3) == 'WDL') {
            $c = '取扱説明書';
        } elseif (substr($book_name, 0, 4) == 'WSM_') {
            $c = 'エンジンワークショップマニュアル';
        } elseif (preg_match('/_OM$/', $book_name)) {
            $c = 'エンジン取扱説明書';
        } elseif (preg_match('/^\d{1}\w+-\w{6}/', $book_name)) {
            $c = 'エンジンパーツマニュアル';
        } elseif (preg_match('/SN/', $book_name)) {
            $c = 'サービスニュース';
        } elseif (preg_match('/hij/', $book_name)) {
            $c = '品質情報連絡表';
        } elseif (preg_match('/SW/', $book_name)) {
            $c = 'サービスワークシート';
        } elseif (preg_match('/CKAA/', $book_name)) {
            $c = '改良工事指示書';
        } elseif (preg_match('/NINKA/', $book_name)) {
            $c = '許認可';
        } elseif (preg_match('/MAINTENANCEGUIDE/', $book_name)) {
            $c = 'メンテナンスガイド';
        } elseif (preg_match('/TROUBLENEWS/', $book_name)) {
            $c = '修理事例集';
        } else {
            $c = 'その他';
        }

        return $c;
    }
}

