<?php

require("simple_html_dom.php");

/**
 * Class DSB
 */
class DSB
{

    private $USERNAME = "";
    private $PASSWORD = "";

    /**
     * DSB constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->USERNAME = $username;
        $this->PASSWORD = $password;
    }

    /**
     * @return a Json object which contains information about your DSB
     */
    public function getData()
    {
        $WEBSERVICE_METHOD = "https://app.dsbcontrol.de/JsonHandler.ashx/GetData";
        $APP_VERSION = "2.5.9";
        $LANGUAGE = "de";
        $OS_VERSION = "";
        $APP_ID = "";
        $DEVICE = "";
        $PUSH_ID = "";
        $BUNDLE_ID = "de.heinekingmedia.inhouse.dsbmobile.web";
        $HMDataType = 1;
        
        $date = date('D M d Y H:i:s O');

        $arguments =
            Array
            (
                "UserId" => $this->USERNAME,
                "UserPw" => $this->PASSWORD,
                "AppVersion" => $APP_VERSION,
                "Language" => $LANGUAGE,
                "OsVersion" => $OS_VERSION,
                "AppId" => $APP_ID,
                "Device" => $DEVICE,
                "PushId" => $PUSH_ID,
                "BundleId" => $BUNDLE_ID,
                "Date" => $date,
                "LastUpdate" => $date
            );

        // json encode
        $arguments_json_encoded = json_encode($arguments);

        // gzip encode
        $arguments_gzip_encoded = gzencode($arguments_json_encoded);

        // Base64 encode
        $arguments_base64_encoded = base64_encode($arguments_gzip_encoded);

        // json encode
        $data =
            json_encode(
                Array(
                    "req" => Array(
                        "Data" => $arguments_base64_encoded,
                        "DataType" => $HMDataType
                    )
                )
            );

        $request = curl_init($WEBSERVICE_METHOD);

        if($request == false)
            return false;

        curl_setopt_array($request,
            array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json;charset=utf-8",
                ),
                CURLOPT_POSTFIELDS => $data
            ));

        // send request
        $response_encoded = curl_exec($request);

        // close Connection
        curl_close($request);

        if($response_encoded == false)
            return false;


        // parse json_encoded
        $response_json = json_decode($response_encoded)->d;

        // decode Base64
        $response_base64_decoded = base64_decode($response_json);

        // decode gzip
        $response_decoded = gzdecode($response_base64_decoded);

        return $response_decoded;
    }

    /**
     * @param $index
     * @return false if the function fails otherwise it returns a Json object which contains your DSB plan
     */
    public function getJson()
    {
        if (false == ($data = $this->getData()))
            return false;

        $json = json_decode($data);
        $array = [];

        foreach ($json->ResultMenuItems[0]->Childs[0]->Root->Childs as $child) {
            $list = trim($child->Title);

            $table = file_get_html($child->Childs[0]->Detail)->find('table', 1);

            if (empty($table))
                continue;

            $titles = [];
            $rowPos = 0;

            foreach ($table->find('tr') as $row) {
                if ($col = $row->find('td')) {
                    foreach ($col as $index => $value)
                        @$array[$list][$rowPos][$titles[$index]] = $value->plaintext;

                    $rowPos++;
                } elseif ($col = $row->find('th')) {
                    foreach ($col as $index => $value)// index not req.
                        $titles[$index] = $value->plaintext;
                }
            }
        }
        return json_encode($array);
    }

}

?>
