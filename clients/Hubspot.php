<?php

class HubSpotException extends Exception { /* silence */ }

class Hubspot extends BaseClient implements IModelCRM {

    use traitsConnection {
        setPutRequest as private;
        setPostRequest as private;
        setGetRequest as private;
    }

    private $api_key = "?hapikey=" . HUBSPOT_API_KEY;
    private $log_file = __DIR__ . '/../logs/hubspot.log.txt';

    protected function __construct($postId) {
        parent::__construct($postId);
    }

    public static function newInstanceWithSharedData(&$array, $postId) {
        $instance = new self($postId);
        $instance->setSharedData($array);
        return $instance;
    }

    public function setSharedData(&$array) {
        $this->sharedArray = &$array;
    }

    public function setFormData(&$data) {
        $this->dataFormatted = $data;
        $this->addDefaultData();
        $this->sanitizeData();
    }

    public function addDefaultData() {
        $this->dataFormatted['domain'] = $this->companyAsDomain($this->dataFormatted['company']);
     }

    public function initConnection() {
        if (!array_key_exists('company', $this->dataFormatted)) { return; }

        // ENSURE HUBSPOT PLUGIN HAS CREATED THE USER
        if (WORKS_WITH_HUBSPOT_PLUGIN && !$this->hubspotPluginCreatedUser()) {
            $error = new HubSpotException("The hubspot plugin doesn't created user for post id: {$this->postId}");
            parent::log($this->log_file, $error);
            throw $error;
        }

        $companyId = $this->getExistingCompanyId();
        
        try {
            $companyId = (!$companyId) ? $this->hubspotCreateCompany() : $companyId;
            $userId = $this->getHubSpotUserByEmail();
            $this->linkUserWithCompany($companyId, $userId);
        } catch (HubSpotException $e) {
            parent::log($this->log_file, $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private function linkUserWithCompany($companyId, $userId) {
        $url = str_replace('{company_id}', $companyId, HUBSPOT_API_CONTACT_ADD_COMPANY);
        $url = str_replace('{contact_id}', $userId, $url);
        $url .= $this->api_key;
        $parameters = null;
        
        $ch = curl_init();
        $this->setPutRequest($ch, $parameters, true, $url, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch));
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode != 200) {
            throw new HubSpotException('Link: ' . serialize($response));
        }
    }

    /**
     * Try to find if the Company already exist in Hubspot
     * @return int The company id if found or null
     * throws : HubSpotException
     */
    private function getExistingCompanyId() {
        $url = str_replace('{domain}', $this->dataFormatted['domain'],
                           HUBSPOT_API_COMPANY_GET_BY_DOMAINE);
        $url .= $this->api_key;

        $data = array(
            'limit' => 1,
            'requestOptions' => array(
                'properties' => [
                    'name',
                    'domain',
                    'createdate'
                ]
            ),
            'offset' => [
                'isPrimary' => true,
                'companyId' => null
            ]
        );
        $data = json_encode($data);

        $ch = curl_init();
        $this->setPostRequest($ch, $data, true, $url, array('Content-type: application/json'));
        $response = json_decode(curl_exec($ch), true);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode != 200) {
            $error = new HubSpotException('Company Exist: ' . serialize($response));
            parent::log($this->log_file, serialize($response));
        }

        $companyId = null;

        if (count($response["results"]) > 0) {
            $companyId = $response['results'][0]['companyId'];
        }

        return $companyId;
    }

    /**
     * Create a company trough Hubspot API
     * @return int Company id from Hubspot
     * throws : HubSpotException
     */
    private function hubspotCreateCompany() {
        $url = HUBSPOT_API_COMPANY_CREATE;
        $url .= $this->api_key;

        $data = array(
            'properties' => array(
                array(
                    'name' => 'domain',
                    'value' => $this->dataFormatted['domain']
                ),
                array(
                    'name' => 'name',
                    'value' => $this->dataFormatted['company']
                ),
                array(
                    'name' => 'description',
                    'value' => $this->getDescription()
                )
            )
        );
        $data = json_encode($data);

        $ch = curl_init();
        $this->setPostRequest($ch, $data, true, $url, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch), true);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($statusCode != 200) {
            throw new HubSpotException('Company: ' . serialize($response));
        }

        return $response['companyId'];
    }

    /**
     * Search for an assoc array that has been built by
     * the hubspot plugin (github: https://github.com/ChromatixAU/cf7-hubspot-forms)
     * @link{https://github.com/ChromatixAU/cf7-hubspot-forms/blob/master/cf7-hubspot-forms-addon.php} line 260
     * return true if the user has been created succefully by the plugin.
     */
    private function hubspotPluginCreatedUser() {
        $postMeta = unserialize(get_post_meta($this->postId, '_cf7hsfi_debug_log', false)[0]);

        return (is_array($postMeta) &&
                ($postMeta['STATUS_CODE'] == 200 || $postMeta['STATUS_CODE'] == 204));
    }

    /**
     * @return int The User Id from Hubspot 
     * Throws : HubSpotExcepition
     */
    private function getHubSpotUserByEmail() {
        $email = $this->dataFormatted['email'];
        $url = str_replace('{email}', $email, HUBSPOT_API_CONTACT_GET_BY_EMAIL);
        $url .= $this->api_key;
        $url .= "&property=vid&property=email";

        $ch = curl_init();
        $this->setGetRequest($ch, true, $url);
        $response = json_decode(curl_exec($ch), true);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode != 200) {
            throw new HubSpotException('User: ' . serialize($response));
        }

        return $response['vid'];
    }

    /**
     * @return string description
     */
    private function getDescription() {
        $dsc = "created the ";
        $dsc .= date(DATE_RFC2822);
        $dsc .= " by {$this->dataFormatted['email']} <\br>";
        $dsc .= " original company's name: #{$this->dataFormatted['company']}";
        return $dsc;
    }

    /**
     * @return string Company name formatted to be a valid domain name dot com
     * Hubspot Requirement
     */
    private function companyAsDomain($company) {
        $domain = str_replace(str_split("éèàçùêë"), str_split('eeacuee'), $company);
        $domain = str_replace(str_split(" ,&~#'*\\$^!:;_/\$£=}{()[]|\"=+"), "", $domain);

        if ($domain[strlen($domain)-1] == '-') {
            $domain = substr($domain, 0, strlen($domain) - 2);
        }
        
        if ($domain[0] == '-') {
            $domain = substr($domain, 1, strlen($domain) - 1);
        }

        return $domain . '.com';
    }
}