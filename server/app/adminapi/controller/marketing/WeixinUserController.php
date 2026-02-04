<?php

namespace app\adminapi\controller\marketing;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\marketing\WeixinUserLists;

class WeixinUserController extends BaseAdminController
{
    public function lists()
    {
        return $this->dataLists(new WeixinUserLists());
    }
}
