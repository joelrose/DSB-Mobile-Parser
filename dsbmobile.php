<?php

require("simple_html_dom.php");

/**
 * Class DSB
 */
class DSB
{

    private $USERNAME = "";
    private $PASSWORD = "";

    private $WEBSERVICE_METHOD = "https://app.dsbcontrol.de/JsonHandler.ashx/GetData";
    private $APP_VERSION = "2.5.9";
    private $LANGUAGE = "de";
    private $OS_VERSION = "19 4.4.2";
    private $APP_ID = "d8c3deab-e8ed-4020-91b9-09fad5173b1a";
    private $DEVICE = "GT-P5210";
    private $PUSH_ID = "APA91bFvpiAGCwO13oJg9ZTgq462oTPF-yQJ5vQaa2wpxL2a0GMaHBzamoksryI_r1rnAmqivwNlAfO2Qxq5umIiWUrVKOfvCpc2soTyVDRAsS-OJYIsO7Vkw0LcjkaRat_U6cEhe47y";
    private $BUNDLE_ID = "de.heinekingmedia.inhouse.dsbmobile.web";
    private $HMDataType = 1;

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
        $date = date('D M d Y H:i:s O');

        $arguments =
            Array
            (
                "UserId" => $this->USERNAME,
                "UserPw" => $this->PASSWORD,
                "AppVersion" => $this->APP_VERSION,
                "Language" => $this->LANGUAGE,
                "OsVersion" => $this->OS_VERSION,
                "AppId" => $this->APP_ID,
                "Device" => $this->DEVICE,
                "PushId" => $this->PUSH_ID,
                "BundleId" => $this->BUNDLE_ID,
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
                        "DataType" => $this->HMDataType
                    )
                )
            );

        $request = curl_init($this->WEBSERVICE_METHOD);

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
    public function getJson($index)
    {
        $data = $this->getData();

        if ($data == false)
            return false;

        $json = json_decode($data);

        if (!isset($json->ResultMenuItems[0]->Childs[0]->Root->Childs[$index]->Childs[0]->Detail))
            return false;

        if (!isset($json->ResultMenuItems[0]->Childs[0]->Title))
            return false;

        $url = $json->ResultMenuItems[0]->Childs[0]->Root->Childs[$index]->Childs[0]->Detail;
        $title = $json->ResultMenuItems[0]->Childs[0]->Title;

        $html = file_get_html($url);

        if ($html == false)
            return false;

        $table = $html->find('table', 1);

        if (empty($table))
            return false;

        $json = '{"' . $title . '":[';

        $total = -1;
        $captions = array();

        foreach ($table->find('tr') as $row) {
            $total++;

            if ($row->find('th')) {
                $count = 0;

                foreach ($row->find('th') as $value) {
                    $captions[$count] = $value->plaintext;
                    $count++;
                }
                continue;
            }

            if ($row->find('td')) {
                $count = 0;

                $values = array();

                foreach ($row->find('td') as $value) {
                    $values[$count] = $value->plaintext;
                    $count++;
                }

                $json .= "{";
                for ($i = 0; $i < count($captions); $i++) {
                    $json .= '"' . $captions[$i] . '":' . ' "' . $values[$i] . '" ' . ",";
                }
                $json = substr($json, 0, -1);
                $json .= "},";

            }
        }

        $json = substr($json, 0, -2);

        $json .= "}]" . ',"total":' . ' "' . $total . '" ' . "}";

        return $json;
    }

}

?>
