<?php

// Your BeApp infor;ation
$beAppName = 'Template';
$beAppSecret = 'asdf';
$beAppId = 113;
$beAppVersion = 1;
$response = array();

// The data is sent to your beApp backend as a json
$data = json_decode(file_get_contents('php://input'), true);

try {
    if (empty($data)) {
        throw new \Exception('BB_ERROR_REQUEST_REJECTED');
    }

    //---------- You want to make sure you're not handling request for other beApps or version--------------------------
    if ($beAppName !== $data['moduleName']
        || $beAppId !== $data['moduleId']
        || $beAppVersion !== $data['moduleVersion']
    ) {
        return null;
    }
    //------------------------------------------------------------------------------------------------------------------

    //-------- The auth part is optional, if you skip it your backend will still work, just less secure ----------------
    // authUser is your beApp short name, 'Template' here
    $authUser = isset($_SERVER ['PHP_AUTH_USER']) ? filter_var($_SERVER ['PHP_AUTH_USER']) : null;
    // authPassword is your web hook auth key, 'asdf' here
    $authPassword = isset($_SERVER ['PHP_AUTH_PW']) ? filter_var($_SERVER ['PHP_AUTH_PW']) : null;

    if ($authUser !== $beAppName.'_'.$beAppId || $authPassword != $beAppSecret
    ) {
        throw new \Exception('BB_ERROR_AUTHORIZATION');
    }
    //------------------------------------------------------------------------------------------------------------------

    // All parameters are in here, in our case, just 'content'
    $params = $data['params'];
    // The transport is either WEB or SMS, regarding how you've sent the request
    $transport = $data['transport'];
    // The Be-Bound userId (UUID)
    $userId = $data['userId'];

    // $data['operation'] contains the operation used
    switch ($data['operation']) {
        // We only have 1 operation in our case, 'send_text'
        case 'send_text':
            $content = $params['content'];
            $length = strlen($content);

            // My response only has 1 attribute, length
            $response = array(
                'length' => $length,
            );
            break;
        default:
            throw new \Exception('BB_ERROR_METHOD_NOT_FOUND');
            break;
    }

    // Your Response should be in the 'params' key or it won't work
    $toSend = array(
        'params' => $response,
    );

} catch (\Exception $e) {
    // Error are returned in the 'error' key
    $toSend = array(
        'error' => $e->getMessage(),
    );
}

header('Content-Type: application/json');
// Your Response should be json encoded
echo json_encode($toSend);
