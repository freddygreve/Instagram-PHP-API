<?php
class Instagram {

    public $ch;
    public $user_agent;
    public $username;
    public $status;
    public $guid;
    public $device_id;
    public $pk;

    public function __construct($username = NULL, $password = NULL) {
        $this->username = $username;

        $resolutions = ['720x1280', '320x480', '480x800', '1024x768', '1280x720', '768x1024', '480x320'];
        $versions = ['GT-N7000', 'SM-N9000', 'GT-I9220', 'GT-I9100'];
        $dpis = ['120', '160', '320', '240'];

        $ver = $versions[array_rand($versions)];
        $dpi = $dpis[array_rand($dpis)];
        $res = $resolutions[array_rand($resolutions)];

        $this->user_agent = 'Instagram 4.'.mt_rand(1,2).'.'.mt_rand(0,2).' Android ('.mt_rand(10,11).'/'.mt_rand(1,3).'.'.mt_rand(3,5).'.'.mt_rand(0,5).'; '.$dpi.'; '.$res.'; samsung; '.$ver.'; '.$ver.'; smdkc210; de_DE)';

        $this->guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        $this->device_id = "android-".$this->guid;

        $data = '{"device_id":"'.$this->device_id.'","guid":"'.$this->guid.'","username":"'.$username.'","password":"'.$password.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';

        $sig = hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916');
        $data = 'signed_body='.$sig.'.'.urlencode($data).'&ig_sig_key_version=4';

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/accounts/login/');
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE,   dirname(__FILE__). '/cookies.txt');            

        $this->status = curl_exec($this->ch);
        $http = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->pk = json_decode($this->status);
        $this->pk = $this->pk->logged_in_user->pk;
        
    }

    public function getuserdata ($username = NULL) {
        if ($username) {
            curl_setopt($this->ch, CURLOPT_URL, 'https://instagram.com/'.$username.'/?__a=1');
        } else {
            curl_setopt($this->ch, CURLOPT_URL, 'https://instagram.com/'.$this->username.'/?__a=1');
        }
        return json_decode(curl_exec($this->ch));
    }

    public function uploadimg ($image = NULL, $description = NULL) {
        $data = [
            'device_timestamp' => time(), 
            'photo' => new CURLFile($image)
        ];
        curl_setopt($this->ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/media/upload/');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $post = curl_exec($this->ch);
        $post = json_decode($post);
        $media_id = $post->media_id;

        $description = preg_replace("/\r|\n/", "", $description);
        $description = addslashes($description);
        $description = utf8_encode($description);

        $data = '{"device_id":"'.$this->device_id.'","guid":"'.$this->guid.'","media_id":"'.$media_id.'","caption":"'.trim($description).'","device_timestamp":"'.time().'","source_type":"5","filter_type":"0","extra":"{}","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';
        $sig = hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916');
        $data = 'signed_body='.$sig.'.'.urlencode($data).'&ig_sig_key_version=4';
        
        curl_setopt($this->ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/media/configure/');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return json_decode(curl_exec($this->ch));
    }
    
    public function follow ($username) {
        curl_setopt($this->ch, CURLOPT_URL, 'https://instagram.com/'.$username.'?__a=1');
        $id = json_decode(curl_exec($this->ch));
        $id = $id->user->id;
        
        $data = '{"device_id":"'.$this->device_id.'","guid":"'.$this->guid.'","user_id":"'.$id.'","_uid":"'.$this->pk.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';
        
        $sig = hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916');
        $data = 'signed_body='.$sig.'.'.urlencode($data).'&ig_sig_key_version=4';
        
        curl_setopt($this->ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/friendships/create/'.$id.'/');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return json_decode(curl_exec($this->ch));
    }
    
    public function unfollow ($username) {
        curl_setopt($this->ch, CURLOPT_URL, 'https://instagram.com/'.$username.'?__a=1');
        $id = json_decode(curl_exec($this->ch));
        $id = $id->user->id;
        
        $data = '{"device_id":"'.$this->device_id.'","guid":"'.$this->guid.'","user_id":"'.$id.'","_uid":"'.$this->pk.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';
        
        $sig = hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916');
        $data = 'signed_body='.$sig.'.'.urlencode($data).'&ig_sig_key_version=4';
        
        curl_setopt($this->ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/friendships/destroy/'.$id.'/');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return json_decode(curl_exec($this->ch));
    }
}
?>
