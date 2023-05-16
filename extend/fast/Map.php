<?php

namespace fast;

class Map
{
    /**
     * 根据地址获取经纬度
     * @param $address
     */
    public static function getLngLat($address)
    {
        $data = [
            'address' => $address,
            'ak' => config('map.ak'),
            'output' => 'json'
        ];
        $url = config('map.baidu_map_url').config('map.geocoding').'?'.http_build_query($data);
        $res = doCurl($url);
        return $res;
    }
}