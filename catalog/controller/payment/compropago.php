<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';

 use CompropagoSdk\Factory\Factory;
 use CompropagoSdk\Client;
 use CompropagoSdk\Tools\Validations;

class ControllerPaymentCompropago extends Controller
{
    /**
     * Configuraciones de los servicios de compropago
     * @var array
     */
    private $compropagoConfig;

    /**
     * Cliente de compropago
     * @var Client
     */
    private $compropagoClient;

    /**
     * Servicios generales de compropago
     * @var Service
     */
    private $compropagoService;


    /**
     * ControllerPaymentCompropago constructor.
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->initServices();
    }


    /**
     * Inicializacion de las clases del SDK
     */
    private function initServices()
    {
        $this->compropagoConfig = array(
            'publickey'     => $this->config->get('compropago_public_key'),
            'privatekey'    => $this->config->get('compropago_secret_key'),
            'live'          => $this->config->get('compropago_mode')
        );

        $this->compropagoClient = new Client(
            $this->compropagoConfig['publickey'],
            $this->compropagoConfig['privatekey'],
            $this->compropagoConfig['live']
        );
    }


    /**
     * @return mixed
     * Carga del template inicial de proveedores
     */
    public function index()
    {
        $this->language->load('payment/compropago');
        $this->load->model('setting/setting');

        $data['text_title']         = $this->language->get('text_title');
        $data['entry_payment_type'] = $this->language->get('entry_payment_type');
        $data['button_confirm']     = $this->language->get('button_confirm');

        $data['comprodata'] = array(
            'providers'     => $this->compropagoClient->api->listProviders(),
            'showlogo'      => $this->config->get('compropago_showlogo'),
            'description'   => $this->config->get('compropago_description'),
            'instrucciones' => $this->config->get('compropago_instrucciones')
        );

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/compropago.tpl', $data);
        }
    }


    /**
     * Procesamiento de la orden de compra
     */
    public function send()
    {
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');

        $order_id       = $this->session->data['order_id'];
        $order_info     = $this->model_checkout_order->getOrder($order_id);
        $products       = $this->cart->getProducts();
        $order_name     = '';

        foreach ($products as $product) {
            $order_name .= $product['name'];
        }

        $data = array(
            'order_id'           => $order_info['order_id'],
            'order_price'        => $order_info['total'],
            'order_name'         => $order_name,
            'customer_name'      => $order_info['payment_firstname'],
            'customer_email'     => $order_info['email'],
            'payment_type'       => $this->request->post['compropagoProvider'],
            'app_client_name'    => 'opencart',
            'app_client_version' => VERSION
        );


        $response = $this->compropagoService->placeOrder($data);


        /**
         * Inicia el registro de transacciones
         */

        $recordTime = time();
        $order_id = $order_info['order_id'];
        $ioIn = base64_encode(json_encode($response));
        $ioOut = base64_encode(json_encode($data));

        // Creacion del query para compropago_orders
        $query = "INSERT INTO " . DB_PREFIX . "compropago_orders (`date`,`modified`,`compropagoId`,`compropagoStatus`,`storeCartId`,`storeOrderId`,`storeExtra`,`ioIn`,`ioOut`)".
            " values (:fecha:,:modified:,':cpid:',':cpstat:',':stcid:',':stoid:',':ste:',':ioin:',':ioout:')";

        $query = str_replace(":fecha:",$recordTime,$query);
        $query = str_replace(":modified:",$recordTime,$query);
        $query = str_replace(":cpid:",$response->id,$query);
        $query = str_replace(":cpstat:",$response->status,$query);
        $query = str_replace(":stcid:",$order_id,$query);
        $query = str_replace(":stoid:",$order_id,$query);
        $query = str_replace(":ste:",'COMPROPAGO_PENDING',$query);
        $query = str_replace(":ioin:",$ioIn,$query);
        $query = str_replace(":ioout:",$ioOut,$query);


        $this->db->query($query);

        $compropagoOrderId = $this->db->getLastId();

        $query2 = "INSERT INTO ".DB_PREFIX."compropago_transactions
        (orderId,date,compropagoId,compropagoStatus,compropagoStatusLast,ioIn,ioOut)
        values (:orderid:,:fecha:,':cpid:',':cpstat:',':cpstatl:',':ioin:',':ioout:')";

        $query2 = str_replace(":orderid:",$compropagoOrderId,$query2);
        $query2 = str_replace(":fecha:",$recordTime,$query2);
        $query2 = str_replace(":cpid:",$response->id,$query2);
        $query2 = str_replace(":cpstat:",$response->status,$query2);
        $query2 = str_replace(":cpstatl:",$response->status,$query2);
        $query2 = str_replace(":ioin:",$ioIn,$query2);
        $query2 = str_replace(":ioout:",$ioOut,$query2);

        $this->db->query($query2);


        /**
         * Update correct status in orders
         */

        $status_update = $this->config->get('compropago_order_status_new_id');

        $query_update = "UPDATE ".DB_PREFIX."order SET order_status_id = $status_update WHERE order_id = $order_id";
        $this->db->query($query_update);


        /**
         * Fin de transacciones
         */


        /**
         * Envio de datos final para render de la vista de recibo
         */

        $json['success'] = htmlspecialchars_decode($this->url->link('payment/compropago/success', 'info_order='.base64_encode(json_encode($response)) , 'SSL'));

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    /**
     * Despliegue del recibo de compra
     */
    public function success()
    {
        $this->language->load('payment/compropago');
        $this->cart->clear();

        if (!$this->request->server['HTTPS']) {
            $data['base'] = HTTP_SERVER;
        } else {
            $data['base'] = HTTPS_SERVER;
        }

        $data['info_order'] = $this->request->get['info_order'];


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_success'),
            'href' => $this->url->link('checkout/success')
        );

        $data['language'] = $this->language->get('code');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        /*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/compropago_success.tpl')) {
            die("Entra if");
            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/compropago_success.tpl', $data));
        } else {
            die("Entra else");
            $this->response->setOutput($this->load->view('payment/compropago_success', $data));
        }*/

        $this->response->setOutput($this->load->view('payment/compropago_success', $data));
    }


    /**
     * WebHook compropago
     */
    public function webhook()
    {
        $this->load->model('setting/setting');

        $request = @file_get_contents('php://input');
        $jsonObj = json_decode($request);

        if($jsonObj){
            if($this->config->get('compropago_status')){

                $compropagoConfig = array(
                    'publickey' => $this->config->get('compropago_public_key'),
                    'privatekey' => $this->config->get('compropago_secret_key'),
                    'live' => (($this->config->get('compropago_mode') == "NO") ? false : true)
                );

                try{
                    $compropagoClient = new Client($compropagoConfig);
                    $compropagoService = new Service($compropagoClient);

                    if(!$respose = $compropagoService->evalAuth()){
                        throw new \Exception("ComproPago Error: Llaves no validas");
                    }

                    if(!Store::validateGateway($compropagoClient)){
                        throw new \Exception("ComproPago Error: La tienda no se encuentra en un modo de ejecución valido");
                    }


                }catch(\Exception $e){
                    echo $e->getMessage();
                }

            }else{
                echo "Compropago is not enabled.";
            }

            //api normalization
            if($jsonObj->api_version=='1.0'){
                $jsonObj->id=$jsonObj->data->object->id;
                $jsonObj->short_id=$jsonObj->data->object->short_id;
            }

            //webhook Test?
            if($jsonObj->id=="ch_00000-000-0000-000000" || $jsonObj->short_id =="000000"){
                echo "Probando el WebHook?, <b>Ruta correcta.</b>";
            }else{
                try{
                    $response  = $compropagoService->verifyOrder($jsonObj->id);

                    if($response->type == 'error'){
                        throw new \Compropago\Sdk\Exception("Error al procesar el numero de orden");
                    }

                    $cp_orders = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."compropago_orders'");
                    $cp_transactions = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX . "compropago_transactions'");

                    if($cp_orders->num_rows == 0 || $cp_transactions->num_rows == 0){
                        throw new \Compropago\Sdk\Exception('ComproPago Tables Not Found');
                    }

                    switch ($response->type){
                        case 'charge.success':
                            $nomestatus = "COMPROPAGO_SUCCESS";
                            break;
                        case 'charge.pending':
                            $nomestatus = "COMPROPAGO_PENDING";
                            break;
                        case 'charge.declined':
                            $nomestatus = "COMPROPAGO_DECLINED";
                            break;
                        case 'charge.expired':
                            $nomestatus = "COMPROPAGO_EXPIRED";
                            break;
                        case 'charge.deleted':
                            $nomestatus = "COMPROPAGO_DELETED";
                            break;
                        case 'charge.canceled':
                            $nomestatus = "COMPROPAGO_CANCELED";
                            break;
                        default:
                            echo 'Invalid Response type';
                    }

                    $thisOrder = $this->db->query("SELECT * FROM ". DB_PREFIX ."compropago_orders WHERE compropagoId = '".$response->id."'");

                    if($thisOrder->num_rows == 0){
                        throw new \Compropago\Sdk\Exception('El número de orden no se encontro en la tienda');
                    }

                    $id = intval($thisOrder->row['storeOrderId']);

                    switch($nomestatus){
                        case 'COMPROPAGO_SUCCESS':
                            $idstorestatus = 5;
                            break;
                        case 'COMPROPAGO_PENDING':
                            $idstorestatus = 1;
                            break;
                        case 'COMPROPAGO_DECLINED':
                            $idstorestatus = 7;
                            break;
                        case 'COMPROPAGO_EXPIRED':
                            $idstorestatus = 14;
                            break;
                        case 'COMPROPAGO_DELETED':
                            $idstorestatus = 7;
                            break;
                        case 'COMPROPAGO_CANCELED':
                            $idstorestatus = 7;
                            break;
                        default:
                            $idstorestatus = 1;
                    }

                    $this->db->query("UPDATE ". DB_PREFIX . "order SET order_status_id = ".$idstorestatus." WHERE order_id = ".$id);

                    $recordTime = time();

                    $this->db->query("UPDATE ". DB_PREFIX ."compropago_orders SET
                    modified = ".$recordTime.",
                    compropagoStatus = '".$response->type."',
                    storeExtra = '".$nomestatus."',
                    WHERE id = ".$thisOrder->row['id']);

                    $ioIn = base64_encode(json_encode($jsonObj));
                    $ioOut = base64_encode(json_encode($response));


                    $query2 = "INSERT INTO ".DB_PREFIX."compropago_transactions
                    (orderId,date,compropagoId,compropagoStatus,compropagoStatusLast,ioIn,ioOut)
                    values (:orderid:,:fecha:,':cpid:',':cpstat:',':cpstatl:',':ioin:',':ioout:')";

                    $query2 = str_replace(":orderid:",$thisOrder->row['id'],$query2);
                    $query2 = str_replace(":fecha:",$recordTime,$query2);
                    $query2 = str_replace(":cpid:",$response->id,$query2);
                    $query2 = str_replace(":cpstat:",$response->type,$query2);
                    $query2 = str_replace(":cpstatl:",$thisOrder->row['compropagoStatus'],$query2);
                    $query2 = str_replace(":ioin:",$ioIn,$query2);
                    $query2 = str_replace(":ioout:",$ioOut,$query2);


                    $this->db->query($query2);


                }catch(\Exception $e){
                    echo $e->getMessage();
                }
            }
        }else{
            echo 'Tipo de Request no Valido';
        }
    }




    /**
     * Verificacion de orden
     * @param $id
     * @return \Compropago\Sdk\json
     */
    public function verifyOrder($id)
    {
        return $this->compropagoService->verifyOrder($id);
    }
}