<?php

namespace Pec;

class PecPayment {

    protected $LoginAccount = null;
    protected $orderId = null;
    protected $amount = null;
    protected $callbackURL = null;
    protected $AdditionalData = null;
    protected $Originator = null;


    public function __construct() {
       
    }

    public function pin($LoginAccount) {
        $this->LoginAccount = $LoginAccount;
    }

    public function init($options = array() ){
        $this->LoginAccount = $options['pin'] ?: null;
        $this->orderId = $options['order_id'] ?: null;
        $this->amount = $options['amount'] ?: null;
        $this->callbackURL = $options['callback_url'] ?: null;
        $this->AdditionalData = $options['AdditionalData'] ?: null;
        $this->Originator = $options['Originator'] ?: null;
    }

    public function setOrderId( $id ) {
        $this->orderId = $id;
    }
    
    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function setCallbackURL($callbackURL) {
        $this->callbackURL = $callbackURL;
    }

    public function setAdditionalData($AdditionalData) {
        $this->AdditionalData = $AdditionalData;
    }

    public function setOriginator($callbackURL) {
        $this->Originator = $Originator;
    }

    public function getOrderId( ) {
        return $this->orderId;
    }
    
    public function getAmount() {
        return $this->amount;
    }

    public function getCallbackURL() {
        return $this->callbackURL;
    }

    public function getLoginAccount() {
        return $this->LoginAccount;
    }

    public function getAdditionalData() {
        return $this->AdditionalData;
    }

    public function getOriginator() {
        return $this->Originator;
    }


    public function pay() {
        if( !$this->getLoginAccount() || !$this->getAmount() || !$this->getOrderId() || !$this->getCallbackURL() )  return false; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->getPaymentURL() );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildSalePaymentXmlString() );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getSaleRequestHeaders() );

    
        $response = curl_exec($ch); 
        curl_close($ch);

        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        $parser = simplexml_load_string($response2);

        if( isset( $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Token ) && !empty($parser->SalePaymentRequestResponse->SalePaymentRequestResult->Token) ){
            $token = $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Token ;
            $message = $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Message;
            $status = $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Status;
            
            return [
                'token' => $token,
                'message' => $message,
                'status' => $status
            ]; 
        } else {
            $message = $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Message;
            $status = $parser->SalePaymentRequestResponse->SalePaymentRequestResult->Status;
            return [
                'message' => $message,
                'status' => $status
            ];
        }
    }

    public function verify($token) {
        if( !$token ) return false; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->getVerifyURL() );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildConfirmPaymentXmlString($token) );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getConfirmRequestHeaders($token) );

        $response = curl_exec($ch); 
        curl_close($ch);

        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        $parser = simplexml_load_string($response2);

        if( isset( $parser->ConfirmPaymentResponse->ConfirmPaymentResult->Status ) && (string)$parser->ConfirmPaymentResponse->ConfirmPaymentResult->Status === '0'  ){
            $token = $parser->ConfirmPaymentResponse->ConfirmPaymentResult->Token ;
            $card_number = $parser->ConfirmPaymentResponse->ConfirmPaymentResult->CardNumberMasked;
            $RRN = $parser->ConfirmPaymentResponse->ConfirmPaymentResult->RRN;
            $status = $parser->ConfirmPaymentResponse->ConfirmPaymentResult->Status;
            
            return [
                'token' => (string)$token,
                'card_number' => (string)$card_number,
                'RRN' => (string)$RRN,
                'status' => (string)$status
            ]; 
        } else {
            $status = $parser->ConfirmPaymentResponse->ConfirmPaymentResult->Status;
            return [
                'status' => (string)$status
            ];
        }
    }


    public function reverse($token) {
        if( !$token ) return false; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->getReverseURL() );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildReversePaymentXmlString($token) );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getReverseRequestHeaders($token) );

        $response = curl_exec($ch); 
        curl_close($ch);

        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        $parser = simplexml_load_string($response2);

        if( isset( $parser->ReversalRequestResponse->ReversalRequestResult->Status ) && (string)$parser->ReversalRequestResponse->ReversalRequestResult->Status === '0' ){
            return true;
        } else 
            return false;
    }

    private function getPaymentURL(){
        return "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx";
    }

    public function getVerifyURL(){
        return "https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx";
    }

    public function getReverseURL(){
        return "https://pec.shaparak.ir/NewIPGServices/Reverse/ReversalService.asmx";
    }

    private function buildSalePaymentXmlString(){

        $SalePaymentRequest = $this->getSalePaymentRequest();
        $orderId = $this->getOrderId();
        $amount = $this->getAmount();
        $callbackURL = $this->getCallbackURL();
        $login_account = $this->getLoginAccount();
        $AdditionalData = $this->getAdditionalData();
        $Originator = $this->getOriginator();

        return '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <SalePaymentRequest xmlns="'.$SalePaymentRequest.'">
            <requestData>
            <LoginAccount>'.$login_account.'</LoginAccount>
            <OrderId>'.$orderId.'</OrderId>
            <Amount>'.$amount.'</Amount>
            <CallBackUrl>'.$callbackURL.'</CallBackUrl>
            <AdditionalData>'.$AdditionalData.'</AdditionalData>
            <Originator>'.$Originator.'</Originator>
            </requestData>
            </SalePaymentRequest>
          </soap:Body>
        </soap:Envelope>';
    }

    private function buildConfirmPaymentXmlString($token){
 
        $ConfirmPaymentRequest = $this->getConfirmPaymentRequest();
        $login_account = $this->getLoginAccount();

        return '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
          <ConfirmPayment xmlns="'.$ConfirmPaymentRequest.'">
            <requestData>
                <LoginAccount>'.$login_account.'</LoginAccount>
                <Token>'.$token.'</Token>
                </requestData>
            </ConfirmPayment>
          </soap:Body>
        </soap:Envelope>';
    }

    private function buildReversePaymentXmlString($token){

        $ReversePaymentRequest = $this->getReversePaymentRequest();
        $login_account = $this->getLoginAccount();

        return '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
          <ReversalRequest xmlns="'.$ReversePaymentRequest.'">
          <requestData>
            <LoginAccount>'.$login_account.'</LoginAccount>
            <Token>'.$token.'</Token>
          </requestData>
        </ReversalRequest>
          </soap:Body>
        </soap:Envelope>';
    }


    private function getSaleRequestHeaders(){
        return array(
            "Content-type: text/xml; charset=utf-8",
            "Accept: text/xml",
            "Host: pec.shaparak.ir", 
            "SOAPAction:".$this->getSaleSoapAction(), 
            "Content-length: ".strlen($this->buildSalePaymentXmlString()),
        );
    }

    private function getConfirmRequestHeaders($token){
        return array(
            "Content-type: text/xml; charset=utf-8",
            "Accept: text/xml",
            "Host: pec.shaparak.ir", 
            "SOAPAction:".$this->getConfirmSoapAction(), 
            "Content-length: ".strlen($this->buildConfirmPaymentXmlString($token)),
        );
    }

    private function getReverseRequestHeaders($token){
        return array(
            "Content-type: text/xml; charset=utf-8",
            "Accept: text/xml",
            "Host: pec.shaparak.ir", 
            "SOAPAction:".$this->getReverseSoapAction(), 
            "Content-length: ".strlen($this->buildReversePaymentXmlString($token)),
        );
    }

    private function getSaleSoapAction(){
        return "https://pec.Shaparak.ir/NewIPGServices/Sale/SaleService/SalePaymentRequest";
    }

    private function getConfirmSoapAction(){
        return "https://pec.Shaparak.ir/NewIPGServices/Confirm/ConfirmService/ConfirmPayment";
    }

    private function getReverseSoapAction(){
        return "https://pec.Shaparak.ir/NewIPGServices/Reversal/ReversalService/ReversalRequest";
    }



    private function getSalePaymentRequest(){
        return "https://pec.Shaparak.ir/NewIPGServices/Sale/SaleService";
    }
    private function getConfirmPaymentRequest(){
        return "https://pec.Shaparak.ir/NewIPGServices/Confirm/ConfirmService";
    }
    private function getReversePaymentRequest(){
        return "https://pec.Shaparak.ir/NewIPGServices/Reversal/ReversalService";
    }



}