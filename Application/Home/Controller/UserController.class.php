<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {

    /* 完善用户信息 */
    public function compeleInfo(){
        if(IS_POST){
            //解决中文乱码
            header('Content-Type:text/html;charset=utf-8');
            $dbStaff = D('staff');
            $userid = $_SESSION['userid'];
            $refData = array(
                'game_id'     => $_POST['gameId'],
                'card_id'     => $_POST['cardNum'],
            );
            //判断是否存在该推荐人以及该手机号是否匹配
            $refStaffExist = $dbStaff->where(array('staff_real' => $_POST['staffName'] ,'mobile' => $_POST['refPhoneNum']))->find();
            if($refStaffExist){
                $refData['referee'] = $refStaffExist['id'];
                $ref = $dbStaff->where('id='.$userid)->save($refData);
                if($ref){
                    $this->success('完善信息成功，正在跳转到个人页面', U('User/index'));exit();
                }

            }
           else{
                $this->error('推荐人和手机号不匹配！', U('User/compeleInfo'));
                //TODO 输出该手机号不存在或者推荐人不存在信息
           }
        }
            $this->display();
    }

    /* 主页面 */
    public function index(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();
        $this->assign('resStaff',$resStaff);
        /*显示剩余任务个数--开始*/
        $dbTaskDone     = D('TaskDone');
        $resDoneCount   = $dbTaskDone -> get_this_week_all_task();
        $doingNo        = $dbTaskDone -> get_count($resDoneCount,'status',1);
        $this->assign('doingNo',$doingNo);
        /*显示剩余任务个数--结束*/
        $this->display();
    }

	/* 我的页面 */
	public function my(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();

        $this->assign('resStaff',$resStaff);
	    $this->display();
	}

    /* 钱包 */
	public function walletDetails(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();

        $this->assign('resStaff',$resStaff);
	    $this->display();
    }

    /* 奖励中心 */
    public function encourage(){
        $dbReward  = M('reward');
        $userid    = $_SESSION['userid'];
        //游戏分红
        $bonusReward = $dbReward->where(array('uid' => $userid , 'type' => 1))->order('create_time desc')->select();
        //任务奖励
        $taskReward = $dbReward->where(array('uid' => $userid , 'type' => 2))->order('create_time desc')->select();
        //推荐奖励
        $spreadReward = $dbReward->where(array('uid' => $userid , 'type' => 3))->order('create_time desc')->select();
        //输出模板
        $this->assign('bonusReward',$bonusReward);
        $this->assign('taskReward',$taskReward);
        $this->assign('spreadReward',$spreadReward);
        $this->display();
    }

    /* 财务管理 */
    public function rechargeWithdrawCash(){
        $dbwithdraw = M('withdraw');
        $dbcharge   = M('charge');
        $uid = $_SESSION['userid'];
        $currMinTime = date('Y-m-01 00:00:00',time());          //获取当前月份最小时间
        //本月提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且大于等于当前月份最小时间
        $condition['create_time'] = array('EGT',$currMinTime);
        $preMonthWithdraw = $dbwithdraw->where($condition)->order('create_time desc')->select();
        //历史提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且小于当前月份最小时间
        $condition['create_time'] = array('LT',$currMinTime);
        $hisMonthWithdraw = $dbwithdraw->where($condition)->order('create_time desc')->select();
        //本月充值
        $condition['pay_id'] = array('eq',$uid );               //等于pay_id且大于等于当前月份最小时间
        $condition['create_time'] = array('EGT',$currMinTime);
        $preMonthCharge = $dbcharge->where($condition)->order('create_time desc')->select();
        //历史充值
        $condition['uid'] = array('eq',$uid );                  //等于uid且小于当前月份最小时间
        $condition['create_time'] = array('LT',$currMinTime);
        $hisMonthCharge = $dbcharge->where($condition)->order('create_time desc')->select();
        //渲染
        $this->assign('refWidthdraw',$preMonthWithdraw);
        $this->assign('hisMonthWithdraw',$hisMonthWithdraw);
        $this->assign('preMonthCharge',$preMonthCharge );
        $this->assign('hisMonthCharge',$hisMonthCharge );
        $this->display();
    }


}
