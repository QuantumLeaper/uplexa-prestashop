<?php
/**
 *      uPlexa Payment Integration with Prestashop
 *	Supported Version : 1.7
 */


// Prestashop 1.7 Compatibility
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class uPlexa extends PaymentModule{

        private $_html = '';
        private $_postErrors = array();

        function __construct(){

                $this->name = "uplexa";
                $this->tab = 'payments_gateways';
                $this->version = '0.1.0';
                $this->author = 'uPlexa Team';
                $this->need_instance = 1;
                $this->bootstrap = true;
                 $this->controllers = array('payment');
       		 $this->is_eu_compatible = 1;
            	 $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
                parent::__construct();

                $this->displayName = $this->l('uPlexa Payments');
                $this->description = $this->l('Module for accepting payments by uPlexa');
                $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        }

         public function install(){

             if(!function_exists('curl_version')) {
                $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.  Please ask your web hosting provider for assistance.');
                 return false;
             }

             if (!parent::install()
                 or !$this->registerHook('payment')
                 or !$this->registerHook('paymentReturn')
                 or !$this->registerHook('displayPDFInvoice')
                 or !$this->registerHook('invoice')
                 or  !$this->registerHook('header')
                 or !$this->registerHook('paymentOptions')


                ) {
                return false;
             }
             $this->active = true;
             return true;
        }

        public function getContent() {
	$output = null;

            if (Tools::isSubmit('submit'.$this->name))
    {
        $uplexa_address = strval(Tools::getValue('UPLEXA_ADDRESS'));
        $uplexa_wallet = strval(Tools::getvalue('UPLEXA_WALLET'));
        if (!$uplexa_address
          || empty($uplexa_address)
          || !Validate::isGenericName($uplexa_address))
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        else
        {
            Configuration::updateValue('UPLEXA_ADDRESS', $uplexa_address);
            Configuration::updateValue('UPLEXA_WALLET', $uplexa_wallet);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }
    return $output.$this->displayForm();
        }



public function displayForm()
{

    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('uPlexa Address'),
                'name' => 'UPLEXA_ADDRESS',
                'size' => 20,
                'required' => true
            ),
            array(
            	'type' => 'text',
            	'label' => $this->l('uPlexa Wallet RPC IP'),
            	'name' => 'UPLEXA_WALLET',
            	'required' => false
            )
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        )
    );

    $helper = new HelperForm();

    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;

    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );

    // Load current value
    $helper->fields_value['UPLEXA_ADDRESS'] = Configuration::get('UPLEXA_ADDRESS');
    $helper->fields_value['UPLEXA_WALLET'] = Configuration::get('UPLEXA_WALLET');

    return $helper->generateForm($fields_form);
}


public function hookPaymentOptions($params)
     {
         if (!$this->active) {
             return;
         }

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText("uPlexa")
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                ->setAdditionalInformation($this->fetch('module:uplexa/views/templates/front/payment_infos.tpl'));

        return [$newOption];
     }


 public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }


}
