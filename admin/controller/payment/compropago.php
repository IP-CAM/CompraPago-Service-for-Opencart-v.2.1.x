<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';


class ControllerPaymentCompropago extends Controller
{
    /**
     * @var array
     * Errores generales del controlador (Obligatoria su declaracion)
     */
    private $error = array();


    /**
     * Carga de la vista principal de configuracion
     */
    public function index()
    {
        # Carga de el arreglo de lenguaje en admin/language/payment/compropago.php
        $this->language->load('payment/compropago');

        # Agrega texto a la etiqueta <title></title> del navegador
        $this->document->setTitle('Compropago Payment Method Configuration');

        # Carga configuraciones iniciales de los modulos opencart
        $this->load->model('setting/setting');

        # Validacion de envio de informacion de configuracion por metodo POST - existencia de llaves publica y privada
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('compropago', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success'). " - <b>". $this->session->data['retro_error']."</b>";
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        # Inclucion de las variables de lenguaje cargadas con $this->lenguage->load('payment/compropago') dentro del arreglo $data
        # $data sera procesado para el render de compropago.tpl

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_secret_key'] = $this->language->get('entry_secret_key');
        $data['entry_public_key'] = $this->language->get('entry_public_key');
        $data['entry_mode'] = $this->language->get('entry_mode');

        $data['entry_select_mode_true'] = $this->language->get('entry_select_mode_true');
        $data['entry_select_mode_false'] = $this->language->get('entry_select_mode_false');

        $data['entry_order_status_new'] = $this->language->get('entry_order_status_new');
        $data['entry_order_status_approve'] = $this->language->get('entry_order_status_approve');
        #$data['entry_order_status_pending'] = $this->language->get('entry_order_status_pending');
        #$data['entry_order_status_declined'] = $this->language->get('entry_order_status_declined');
        #$data['entry_order_status_cancel'] = $this->language->get('entry_order_status_cancel');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_db_prefix'] = $this->language->get('entry_db_prefix');

        $data['entry_showlogo'] = $this->language->get('entry_showlogo');
        $data['entry_description'] = $this->language->get('entry_description');
        $data['entry_instrucciones'] = $this->language->get('entry_instrucciones');

        $data['help_secret_key'] = $this->language->get('help_secret_key');
        $data['help_public_key'] = $this->language->get('help_public_key');
        $data['help_mode'] = $this->language->get('help_mode');

        #$data['help_db_prefix'] = $this->language->get('help_db_prefix');

        $data['button_save'] = $this->language->get('text_button_save');
        $data['button_cancel'] = $this->language->get('text_button_cancel');

        $data['tab_plugin_configurations'] = $this->language->get('tab_plugin_configurations');
        $data['tab_display_configurations'] = $this->language->get('tab_display_configurations');
        $data['tab_estatus_configurations'] = $this->language->get('tab_estatus_configurations');


        /**
         * Validaciones de existencia de errores
         */

        # Errores generales
        $data['error_warning'] = isset($this->error['warning']) ?  $this->error['warning'] : '';

        # Error de llave privada
        $data['error_secret_key'] = isset($this->error['secret_key']) ? $this->error['secret_key'] : '';

        # error de llave publica
        $data['error_public_key'] = isset($this->error['public_key']) ? $this->error['public_key'] : '';


        /**
         * Inclucion de los breadcrums en la cabecera de la vista de configuracion
         * El orden de inclucion de los breadcrums, sera el mismo al desplegarse
         * Ej: $data['breadcrums'][0] / $data['breadcrums'][1]
         */


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(

            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/compropago', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('payment/compropago', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');


        /**
         * Validaciones de existencias de datos para la peticion
         */

        $data['compropago_secret_key'] = isset($this->request->post['compropago_secret_key']) ? $this->request->post['compropago_secret_key'] : $this->config->get('compropago_secret_key');
        $data['compropago_public_key'] = isset($this->request->post['compropago_public_key']) ? $this->request->post['compropago_public_key'] : $this->config->get('compropago_public_key');
        $data['compropago_mode'] = isset($this->request->post['compropago_mode']) ? $this->request->post['compropago_mode'] : $this->config->get('compropago_mode');
        $data['compropago_order_status_new_id'] = isset($this->request->post['compropago_order_status_new_id']) ? $this->request->post['compropago_order_status_new_id'] : $this->config->get('compropago_order_status_new_id');
        $data['compropago_order_status_approve_id'] = isset($this->request->post['compropago_order_status_approve_id']) ? $this->request->post['compropago_order_status_approve_id'] : $this->config->get('compropago_order_status_approve_id');
        #$data['compropago_order_status_pending_id'] = isset($this->request->post['compropago_order_status_pending_id']) ? $this->request->post['compropago_order_status_pending_id'] : $this->config->get('compropago_order_status_pending_id');
        #$data['compropago_order_status_declined_id'] = isset($this->request->post['compropago_order_status_declined_id']) ? $this->request->post['compropago_order_status_declined_id'] : $this->config->get('compropago_order_status_declined_id');
        #$data['compropago_order_status_cancel_id'] = isset($this->request->post['compropago_order_status_cancel_id']) ? $this->request->post['compropago_order_status_cancel_id'] : $this->config->get('compropago_order_status_cancel_id');
        $data['compropago_sort_order'] = isset($this->request->post['compropago_sort_order']) ? $this->request->post['compropago_sort_order'] : $this->config->get('compropago_sort_order');
        $data['compropago_status'] = isset($this->request->post['compropago_status']) ? $this->request->post['compropago_status'] : $this->config->get('compropago_status');
        $data['compropago_instrucciones'] = isset($this->request->post['compropago_instrucciones']) ? $this->request->post['compropago_instrucciones'] : $this->config->get('compropago_instrucciones');
        $data['compropago_description'] = isset($this->request->post['compropago_description']) ? $this->request->post['compropago_description'] : $this->config->get('compropago_description');
        $data['compropago_showlogo'] = isset($this->request->post['compropago_showlogo']) ? $this->request->post['compropago_showlogo'] : $this->config->get('compropago_showlogo');

        #$data['compropago_db_prefix'] = isset($this->request->post['compropago_db_prefix']) ? $this->request->post['compropago_db_prefix'] : $this->config->get('compropago_db_prefix');


        /**
         * Inicio del renderizado de la vista de configuracion
         */

        if($this->hookRetroalimentacion()){
            $data['compropago_retro_hook'] = true;

            $text = $this->getErrorText();

            $data['compropago_retro_text'] = $text;
            $this->session->data['retro_error'] = $text;
        }


        # carga del modulo de estatus de peticion
        $this->load->model('localisation/order_status');
        # recuperacion de todos los estatus
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        # Inclucion de las partes genericas de la vista de panel de administracion
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        # render final de la vista de configuracion del modulo
        $this->response->setOutput($this->load->view('payment/compropago.tpl', $data));
    }

    /**
     * Obtiene el error de retroalimentacion.
     * @return string
     */
    private function getErrorText()
    {
        $final = "";
        foreach($this->error as $text){
            $final .= $text;
        }

        return $final;
    }

    /**
     * Se encarga de generar las retroalimentaciones para el usuario
     * @return bool
     */
    private function hookRetroalimentacion()
    {
        $flagerror = false;

        if($this->config->get('compropago_status') == 1){
            if( !empty($this->config->get('compropago_public_key')) && !empty($this->config->get('compropago_secret_key')) ){

                if($this->config->get('compropago_mode')=='SI'){
                    $moduleLive=true;
                }else {
                    $moduleLive=false;
                }

                $compropagoConfig = array(
                    "publickey" => $this->config->get('compropago_public_key'),
                    "privatekey" => $this->config->get('compropago_secret_key'),
                    "live" => $moduleLive
                );

                try{
                    $compropagoClient = new Client($compropagoConfig);
                    $compropagoService = new Service($compropagoClient);
                    //eval keys
                    if(!$compropagoResponse = $compropagoService->evalAuth()){
                        $this->error[] = 'Invalid Keys, The Public Key and Private Key must be valid before using this module.';
                        $flagerror = true;
                    }else{
                        if($compropagoResponse->mode_key != $compropagoResponse->livemode){
                            // compropagoKey vs compropago Mode
                            $this->error[] = 'Your Keys and Your ComproPago account are set to different Modes.';
                            $flagerror = true;
                        }else{
                            if($moduleLive != $compropagoResponse->livemode){
                                // store Mode vs compropago Mode
                                $this->error[] = 'Your Store and Your ComproPago account are set to different Modes.';
                                $flagerror = true;
                            }else{
                                if($moduleLive != $compropagoResponse->mode_key){
                                    // store Mode vs compropago Keys
                                    $this->error[] = 'ComproPago ALERT:Your Keys are for a different Mode.';
                                    $flagerror = true;
                                }else{
                                    if(!$compropagoResponse->mode_key && !$compropagoResponse->livemode){
                                        //can process orders but watch out, NOT live operations just testing
                                        $this->error[] = 'WARNING: ComproPago account is Running in TEST Mode, NO REAL OPERATIONS';
                                        $flagerror = true;
                                    }else{
                                        $this->errmsg = '';
                                    }
                                }
                            }
                        }
                    }
                }catch (\Exception $e) {
                    //something went wrong on the SDK side
                    $this->error[] = $e->getMessage(); //may not be show or translated
                    $flagerror = true;
                }
            }else{
                $this->error[] = 'The Public Key and Private Key must be set before using ComproPago';
                $flagerror = true;
            }
        }else{
            $this->error[] = 'ComproPago is not Enabled';
            $this->controlVision='no';
            $flagerror = true;
        }

        return $flagerror;
    }


    /**
     * @return bool
     * Validacion de error por llaves
     */
    private function validate()
    {
        if (!$this->request->post['compropago_secret_key']) {
            $this->error['secret_key'] = $this->language->get('error_secret_key');
        }

        if (!$this->request->post['compropago_public_key']) {
            $this->error['public_key'] = $this->language->get('error_public_key');
        }

        return !$this->error;
    }


    /**
     * 
     */
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "compropago_orders` ("
            . "`id` int(11) NOT NULL AUTO_INCREMENT,"
            . "`date` int(11) NOT NULL,"
            . "`modified` int(11) NOT NULL,"
            . "`compropagoId` varchar(50) NOT NULL,"
            . "`compropagoStatus` varchar(50) NOT NULL,"
            . "`storeCartId` varchar(255) NOT NULL,"
            . "`storeOrderId` varchar(255) NOT NULL,"
            . "`storeExtra` varchar(255) NOT NULL,"
            . "`ioIn` mediumtext,"
            . "`ioOut` mediumtext,"
            . "PRIMARY KEY (`id`), UNIQUE KEY (`compropagoId`)"
            . ")ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "compropago_transactions` ("
            . "`id` int(11) AUTO_INCREMENT,"
            . "`order_id` int(11) NOT NULL,"
            . "`date` int(11) NOT NULL,"
            . "`compropagoId` varchar(50) NOT NULL,"
            . "`compropagoStatus` varchar(50) NOT NULL,"
            . "`compropagoStatusLast` varchar(50) NOT NULL,"
            . "`ioIn` mediumtext,"
            . "`ioOut` mediumtext,"
            . "PRIMARY KEY (`id`)"
            . ")ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci  AUTO_INCREMENT=1;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "compropago_webhook_transactions` ("
            . "`id` integer NOT NULL AUTO_INCREMENT,"
            . "`webhookId` varchar(50) NOT NULL,"
            . "`webhookUrl` varchar(300) NOT NULL,"
            . "`updated` integer NOT NULL,"
            . "`status` varchar(50) NOT NULL,"
            . "PRIMARY KEY (`id`)"
            . ")ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci  AUTO_INCREMENT=1;");
    }

    /**
     * Obtener querys con prefijo y eleminar tablas de compropago
     */
    public function uninstall()
    {
        
    }
}