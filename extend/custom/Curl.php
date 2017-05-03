<?php

namespace custom;

/**
 * Curl
 *
 *
 *
 *
 */


class Curl {
    protected $status = "";

    /**
     * get请求
     *
     * @param string url
     * @return string
     */
    public function get($url, $header="") {
        // 调用curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    /**
     * post请求
     *
     * @param string url
     * @param array data
     * @return string
     */
    public function post($url, $data, $header="") {
        // 接收到数据是数组时转化为json
        if(is_array($data)){
            $data = http_build_query($data);
        }
        // 调用curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }


    /**
     * 获取http状态码
     *
     * @return number
     */
    public function getstatus() {
        return $this->status;
    }
}
?>
