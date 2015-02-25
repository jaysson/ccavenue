<?php

namespace Langoor\CCAvenue;

class Gateway
{
    private $merchantId;
    private $url;
    private $workingKey;

    public function __construct($merchantId, $url, $workingKey)
    {
        $this->merchantId = $merchantId;
        $this->url = $url;
        $this->workingKey = $workingKey;
    }

    public function getchecksum($Amount, $OrderId)
    {
        $str = $this->merchantId . "|$OrderId|$Amount|" . $this->url . "|" . $this->workingKey;
        $adler = 1;
        $adler = $this->adler32($adler, $str);
        return $adler;
    }

    public function verifychecksum($OrderId, $Amount, $AuthDesc, $CheckSum)
    {
        $str = $this->merchantId . "|$OrderId|$Amount|$AuthDesc|" . $this->workingKey;
        $adler = 1;
        $adler = $this->adler32($adler, $str);
        if ($adler == $CheckSum) {
            return true;
        } else {
            return false;
        }
    }

    private function adler32($adler, $str)
    {
        $BASE = 65521;
        $s1 = $adler & 0xffff;
        $s2 = ($adler >> 16) & 0xffff;
        for ($i = 0; $i < strlen($str); $i++) {
            $s1 = ($s1 + Ord($str[$i])) % $BASE;
            $s2 = ($s2 + $s1) % $BASE;
            //echo "s1 : $s1 <BR> s2 : $s2 <BR>";

        }
        return $this->leftShift($s2, 16) + $s1;
    }

    private function leftShift($str, $num)
    {
        $str = DecBin($str);

        for ($i = 0; $i < (64 - strlen($str)); $i++)
            $str = "0" . $str;

        for ($i = 0; $i < $num; $i++) {
            $str = $str . "0";
            $str = substr($str, 1);
            //echo "str : $str <BR>";
        }
        return $this->cdec($str);
    }

    private function cdec($num)
    {
        $dec = 0;
        for ($n = 0; $n < strlen($num); $n++) {
            $temp = $num[$n];
            $dec = $dec + $temp * pow(2, strlen($num) - $n - 1);
        }
        return $dec;
    }

    public function form($amount, $orderId, $billingProfile = ['name' => '', 'address' => '', 'country' => '', 'state' => '', 'city' => '', 'zip' => '', 'phone' => '', 'email' => ''], $deliveryProfile = ['name' => '', 'address' => '', 'country' => '', 'state' => '', 'city' => '', 'phone' => '', 'notes' => '', 'zip' => ''], $parameters = [])
    {
        $form = '<form id="ccavenue" method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp">
	<input type=hidden name="Merchant_Id" value="' . $this->merchantId . '">
	<input type=hidden name="Amount" value="' . $amount . '">
	<input type=hidden name="Order_Id" value="' . $orderId . '">
	<input type=hidden name="Redirect_Url" value="' . $this->url . '">
	<input type=hidden name="Checksum" value="' . $this->getchecksum($amount, $orderId) . '">
	<input type="hidden" name="billing_cust_name" value="' . $billingProfile["name"] . '">
	<input type="hidden" name="billing_cust_address" value="' . $billingProfile["address"] . '">
	<input type="hidden" name="billing_cust_country" value="' . $billingProfile["country"] . '">
	<input type="hidden" name="billing_cust_state" value="' . $billingProfile["state"] . '">
	<input type="hidden" name="billing_cust_city" value="' . $billingProfile["city"] . '">
	<input type="hidden" name="billing_zip" value="' . $billingProfile["zip"] . '">
	<input type="hidden" name="billing_cust_tel" value="' . $billingProfile["phone"] . '">
	<input type="hidden" name="billing_cust_email" value="' . $billingProfile["email"] . '">';
        if ($billingProfile) {
            $form .= '<input type="hidden" name="delivery_cust_name" value="' . $deliveryProfile["name"] . '">
	<input type="hidden" name="delivery_cust_address" value="' . $deliveryProfile["address"] . '">
	<input type="hidden" name="delivery_cust_country" value="' . $deliveryProfile["country"] . '">
	<input type="hidden" name="delivery_cust_state" value="' . $deliveryProfile["state"] . '">
	<input type="hidden" name="delivery_cust_city" value="' . $deliveryProfile["city"] . '">
	<input type="hidden" name="delivery_zip_code" value="' . $deliveryProfile["zip"] . '">
	<input type="hidden" name="delivery_cust_tel" value="' . $deliveryProfile["phone"] . '">
	<input type="hidden" name="delivery_cust_notes" value="' . $deliveryProfile["notes"] . '">';
        }
        foreach ($parameters as $name => $value) {
            $form .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
        $form .= '<input type="submit" class="btn btn-primary" value="submit">
	</form>';
        return $form;
    }

}