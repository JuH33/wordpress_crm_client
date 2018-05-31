<?php

//===========================================
//                CONSTANTS
//===========================================

// GLOBAL
define("SKIP_MAIL", false);
define("DEBUG", true);

// MAPOTEMPO
define('MAPOTEMPO_API_KEY', '58eecd3354ba35ba52caa9cb5dd78023');
define('MAPOTEMPO_URL_API_CUSTOMER', 'https://app.mapotempo.com/api/0.1/customers/');
define('MAPOTEMPO_URL_API_USER', 'https://app.mapotempo.com/api/0.1/users');
define('MAPOTEMPO_URL_USERS_CUSTOMER', 'https://app.mapotempo.com/api/0.1/customers/');

define('MAPOTEMPO_TEMPLATE_ID_FR', 1114);
define('MAPOTEMPO_TEMPLATE_ID_EN', 1916);
define('MAPOTEMPO_TEMPLATE_ID_MA', 1387);
define('MAPOTEMPO_TEMPLATE_ID_HE', 2845);
define('MAPOTEMPO_TEMPLATE_ID_PT', 2848);

// INES
define('INES_URL', 'https://secure.inescrm.com/InesWebFormHandler/Main.aspx');

// HubSpot
define('WORKS_WITH_HUBSPOT_PLUGIN', true);
define('HUBSPOT_API_KEY', 'e7738adf-50c9-48c3-b229-f03f74f5019c');
define('HUBSPOT_API_COMPANY_CREATE', 'https://api.hubapi.com/companies/v2/companies');
define('HUBSPOT_API_COMPANY_GET_BY_DOMAINE', 'https://api.hubapi.com/companies/v2/domains/{domain}/companies');
define('HUBSPOT_API_CONTACT_ADD_COMPANY', 'https://api.hubapi.com/companies/v2/companies/{company_id}/contacts/{contact_id}');
define('HUBSPOT_API_CONTACT_GET_BY_EMAIL', 'https://api.hubapi.com/contacts/v1/contact/email/{email}/profile');