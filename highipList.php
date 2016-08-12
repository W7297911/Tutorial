<?php
/**
 * Created by PhpStorm.
 * User: Zy
 * Date: 2016/6/22
 * Time: 9:42
 */
require_once __DIR__ . "/../../../php/index.php";

class HighIpList extends \Http\UserAuthHttpKernel
{
    protected $validateKeys = ['page' => 1, 'nub' => 10, 'kwd' => ''];
    protected $rules = [
        'page' => 'integer',
        'nub' => 'integer',
        'kwd' => 'string',
    ];
    protected $validateMessage = [
        'page.integer' => 'page 必须是整数',
        'nub.integer' => 'nub 必须是整数',
        'kwd.string' => 'k must be string',
        'id.integer' => 'id must be integer'
    ];

    public function handle()
    {
        $userId = $this->user->id;
        $kwd = $this->input['kwd'];
        $nub = $this->input['nub'];
        $list = \Model\HighIp::query()->where('user_id', $userId)->where('high_ip', 'like', '%' . $kwd . '%')
            ->orderBy('add_date', 'desc')->paginate($nub);
        $listData = [];
        foreach ($list as $item) {
            $except = ['date'];
            $itemData = array_except($item->toArray(), $except);
            $itemData = array_merge($itemData, [
                'firewallflow' => $item->firewallflow . 'kbps',
                'start_time' => date('Y-m-d', strtotime($item->start_time)),
                'end_time' => date('Y-m-d', strtotime($item->end_time)),
            ]);
            if ($item->isExpired()) {
                $itemData['sb'] = 0;
            } else {
                $itemData['sb'] = 1;
            }
            $listData[] = $itemData;
        }
        if (!empty($listData)) {
            $data['list'] = $listData;
            $data['num'] = count($list);
            $data['sum'] = $list->total();
            $data['return'] = 'true';
            $data['msg'] = "获取成功";
        } else {
            $data['list'] = '';
            $data['num'] = 0;
            $data['sum'] = 0;
            $data['return'] = 'false';
            $data['msg'] = "获取失败";
        }
        $this->output = $data;

    }
}

$api = new HighIpList();
$api->run();