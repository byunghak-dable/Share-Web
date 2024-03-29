<?php

namespace app\models;

require 'app/data/UserInfo.php';
require 'app/data/AdminData.php';

use core\App;
use core\mvc\Model;

class UserModel extends Model
{
    private $tableName = 'user';
    private $dataClass = 'app\data\UserInfo';

    // 회원가입 성공 시 데이터베이스에 회원 정보 저장하는 메소드
    public function uploadUser($userID, $password, $nickName, $sex)
    {

        App::get('database')->insertData('user', [
            'user_id' => '"' . $userID . '"',
            'password' => '"' . $password . '"',
            'nick_name' => '"' . $nickName . '"',
            'sex' => '"' . $sex . '"'
        ]);
    }

    // 회원가입 시 중복 체크를 하기 위해 사용자 정보를 db로부터 가져오는 메소드
    public function fetchAllUser()
    {
        $tableName = 'user';

        $statement = App::get('database')->selectTable($tableName);
        $userList = App::get('database')->fetchAllByArray($statement);

        $this->returnedData = ['userList' => $userList];
    }

    public function checkLoginInput($userID, $password)
    {
        $tableName = 'user';
        $dataClass = 'app\data\UserInfo';

        $userInfo = App::get('database')->fetchLoginData($tableName, $userID, $password, $dataClass);

        if ($userInfo == null) {
            $message = '아이디 혹은 비밀번호를 확인해주세요';
            $this->returnedData = [
                'userID' => $userID,
                'loginError' => $message
            ];
        }
        return $userInfo;
    }

    public function startSession($userInfo)
    {
        $userID =  $userInfo[0]->getUserID();
        $nickName = $userInfo[0]->getNickName();
        $sex = $userInfo[0]->getSex();
        $admin = $userInfo[0]->getIsAdmin();
        $donationMoney = $userInfo[0]->getDonationMoney();

        // 세션에 사용자 정보를 등록한다
        $_SESSION['userID'] = $userID;
        $_SESSION['nickName'] = $nickName;
        $_SESSION['sex'] = $sex;
        $_SESSION['admin'] = $admin;
        $_SESSION['donationMoney'] = $donationMoney;
    }

    public function uploadDonationMoney($nickName, $donationMoney)
    {
        $columnKeyData['nick_name'] = '"' . $nickName . '"';
        $keyValueData['donation_money'] = $donationMoney;
        App::get('database')->updateByName($this->tableName, $columnKeyData, $keyValueData);
    }

    function fetchDonationData($nickName)
    {
        $keyValueData['nick_name'] = '"' . $nickName . '"';
        $userInfo = App::get('database')->fetchValueByName($this->tableName,  $keyValueData, $this->dataClass);
        return $userInfo[0]->getDonationMoney();
    }

    // 관리자 페이지 용 데이터 저장하기
    public function uploadAdminData($ip, $userAgent)
    {
        $tableName = 'admin';
        $country = $this->getCountry($ip);
        $browser = $this->getBrowser($userAgent);
        $keyValue = [
            'country' => '"' . $country . '"',
            'browser' => '"' . $browser . '"'
        ];
        App::get('database')->insertData($tableName, $keyValue);
    }

    public function fetchAdminData()
    {
        $adminTable = 'admin';
        $userTable = 'user';
        $adminDataClass  = 'app\data\AdminData';
        $userDataClass = 'app\data\UserInfo';

        $adminStatement = App::get('database')->selectTable($adminTable);
        $userStatement = App::get('database')->selectTable($userTable);

        $adminDataBundle = App::get('database')->fetchAllValues($adminStatement, $adminDataClass);
        $userList = App::get('database')->fetchAllValues($userStatement, $userDataClass);

        $visitorCountries = $this->getVisitorCountries($adminDataBundle);
        $monthlyVisitor = $this->getMonthlyVisior($adminDataBundle);
        $visitorBrowser = $this->getVisitorBrowser($adminDataBundle);
        $memberSexRatio = $this->getMemberSexRatio($userList);

        $this->returnedData = [
            'visitorCountries' => $visitorCountries,
            'monthlyVisitor' => $monthlyVisitor,
            'visitorBrowser' => $visitorBrowser,
            'memberSexRatio' => $memberSexRatio
        ];
    }

    // ip 정보를 국가 정보로 변환하는 메소드
    private function getCountry($ip)
    {
        // Use JSON encoded string and converts 
        // it into a PHP variable 
        $ipData = @json_decode(file_get_contents(
            "http://www.geoplugin.net/json.gp?ip=" . $ip
        ));

        return $ipData->geoplugin_countryName;
    }

    private function getBrowser($userAgent)
    {
        $arr_browsers = ["Opera", "Edge", "Chrome", "Safari", "Firefox", "MSIE", "Trident"];

        $user_browser = '';
        foreach ($arr_browsers as $browser) {
            if (strpos($userAgent, $browser) !== false) {
                $user_browser = $browser;
                break;
            }
        }

        switch ($user_browser) {
            case 'MSIE':
                $user_browser = 'Internet Explorer';
                break;

            case 'Trident':
                $user_browser = 'Internet Explorer';
                break;

            case 'Edge':
                $user_browser = 'Microsoft Edge';
                break;
        }

        return $user_browser;
    }

    // 나라별 사용자를 저장하는 배열을 반환
    public function getVisitorCountries($adminDataBundle)
    {
        // 나라별 방문자들 수를 담고있는 key-value 배열 생성
        $visitorCountries = array();

        foreach ($adminDataBundle as $adminData) {
            $country = $adminData->getCountry();

            if (array_key_exists($country, $visitorCountries)) {

                $visitorCountries[$country] = $visitorCountries[$country] + 1;
            } else {

                $visitorCountries[$country] = 1;
            }
        }
        $chartDataForm =  $this->convertToChartData($visitorCountries, 'country', 'visitor');
        return $chartDataForm;
    }

    // 월별 사용자들을 저장하는 배열을 반환
    private function getMonthlyVisior($adminDataBundle)
    {
        $monthlyVisitor = array();

        // 1~12월까지의 key 값 생성, value는 모두 0
        for ($month = 1; $month < 13; $month++) {
            $monthlyVisitor[$month . '월'] = 0;
        }

        // 월별 사용자들을 분리해서 mothlyVisitor에 저장
        foreach ($adminDataBundle as $adminData) {
            $timeStamp = $adminData->getTimeStamp();
            $month = date('n', strtotime($timeStamp));
            $monthlyVisitor[$month . '월'] = $monthlyVisitor[$month . '월'] + 1;
        }

        $chartDataForm =  $this->convertToChartData($monthlyVisitor, 'month', 'visitor');
        return $chartDataForm;
    }

    private function getVisitorBrowser($adminDataBundle)
    {
        $visitorBrowser = array();

        foreach ($adminDataBundle as $adminData) {
            $browser = $adminData->getBrowser();
            if (array_key_exists($browser, $visitorBrowser)) {

                $visitorBrowser[$browser] = $visitorBrowser[$browser] + 1;
            } else {

                $visitorBrowser[$browser] = 1;
            }
        }

        $chartDataForm = $this->convertToChartData($visitorBrowser, 'browser', 'visitor');
        return $chartDataForm;
    }

    private function getMemberSexRatio($userList)
    {
        $memberSexRatio = array();

        foreach ($userList as $user) {
            $memberSex = $user->getSex();
            if (array_key_exists($memberSex, $memberSexRatio)) {

                $memberSexRatio[$memberSex] = $memberSexRatio[$memberSex] + 1;
            } else {

                $memberSexRatio[$memberSex] = 1;
            }
        }

        $chartDataForm = $this->convertToChartData($memberSexRatio, '성별', '회원 수');
        return $chartDataForm;
    }

    private function convertToChartData($associativeArray, $keyHeader, $valueHeader)
    {
        $chartDataForm = array([$keyHeader, $valueHeader]);
        for ($i = 0; $i < count(array_keys($associativeArray)); $i++) {

            $key = array_keys($associativeArray)[$i];
            $value = array_values($associativeArray)[$i];
            array_push($chartDataForm, [$key, $value]);
        }
        return $chartDataForm;
    }
}
