<?php

class API {

    public $user;

    //Method to request for token in Laravel application
    function getCsrfToken()
    {
        $url = 'http://127.0.0.1:8000/csrf';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $token = curl_exec($ch);
        curl_close($ch);

        return $token;
    }

    function checkServerStatus($http_code, $response)
    {
        if($http_code >= 400)
            return 'Error';
        else
            return $response;
    }

    function validateDataTypeIfArray($parameter)
    {
        if (!is_array($parameter)) {
            return (object) [
                'error' => true,
                'message' => "Error: expected parameter to be an array, got " . gettype($parameter)
            ];
        } else {
            return (object) [
                'error' => false,
                'message' => ""
            ];
        }
    }

    /* 
        # Array must contain elements as follows:
            'last_name'
            'first_name'
            'username'
            'password'
            'confirm_password'

        # returns 'success' if success request
        # returns Object if validation fails from application
        # returns 'error' if something went wrong
    */
    function addUserToRequest($request)//$request parameter must be array
    {
        $checkIfArray = $this->validateDataTypeIfArray($request);

        if($checkIfArray->error)
            return $checkIfArray->message;
        
        $token = $this->getCsrfToken();

        $url = 'http://127.0.0.1:8000/api/pre_registration';

        $request['_token'] = $token;
        $request['api'] = true;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($this->checkServerStatus($status, $response));
    }

    function changePassword($request)//$request parameter must be array
    {
        $checkIfArray = $this->validateDataTypeIfArray($request);

        if($checkIfArray->error)
            return $checkIfArray->message;

        $token = $this->getCsrfToken();

        $url = 'http://127.0.0.1:8000/api/change_password';

        $request['_token'] = $token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($this->checkServerStatus($status, $response));
    }

    function login($request)
    {
        $checkIfArray = $this->validateDataTypeIfArray($request);

        if($checkIfArray->error)
            return $checkIfArray->message;

        $token = $this->getCsrfToken();

        $url = 'http://127.0.0.1:8000/api/login';

        $request['_token'] = $token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($status < 400)
            return (array) [$token, $response];

        return $response;
    }
}

$api = new API();
/* 
    // pre_registration
    // $response = $api->addUserToRequest(
    //     (array) [
    //         'first_name' => 'Natasha',
    //         'last_name' => 'Worth',
    //         'username' => 'nwusername',
    //         'password' => 'NWorth@12345',
    //         'confirm_password' => 'NWorth@12345',
    //     ]
    // );

    // change password
    // $response = $api->changePassword(
    //     (array) [
    //         'username' => 'jdusername',
    //         'password' => 'JDoe@12345',
    //         'confirm_password' => 'JDoe@12345'
    //     ]
    // );

    // print_r($response);

    // change password
    
    // //print_r(json_decode($response));
    // print_r($response);
*/
$response = $api->login(
    (array) [
        'username' => 'jdusername',
        'password' => 'JDoe@12345',
    ]
);

?>

<script>
    if(('<?php echo gettype($response) ?>').toString() == 'array')
    {
        if(typeof `<?php echo $response[1] ?>` == 'object'){
            if('error' in JSON.parse(`<?php echo $response[1] ?>`))
                console.log(JSON.parse(`<?php echo $response[1] ?>`).error)
            else
                console.log('some error')
        }else
            window.location.href = "http://127.0.0.1:8000/redirect?_token="+`<?php echo $response[0] ?>`+"&user="+`<?php echo $response[1] ?>`
    }else{
        console.log(`<?php print_r ($response) ?>`)
    }
</script>