<?php

namespace Pro_Woocommerce\Woocommerce;

class Customer {

    private $id;
    private $username;
    private $first_name;
    private $last_name;
    private $job_title;
    private $email;
    private $phone;
    private $street;
    private $street_additional;
    private $zip_code;
    private $city;
    private $company;
    private $company_tax_id;
    private $website;
    private $status;

    public function __construct($id_or_email){
        if(is_numeric($id_or_email)){
            $user = get_user_by('id', $id_or_email);
        } else {
            $user = get_user_by('email', $id_or_email);
        }
        if($user){
            $this->fetch($user);
        }
    }

    public function fetch($user) {
        $this->id = $user->ID;
        $this->username = $user->data->display_name;
        $this->first_name = get_user_meta($this->id, 'first_name', true);
        $this->last_name = get_user_meta($this->id, 'last_name', true);
        $this->job_title = get_user_meta($this->id, 'billing_job_title', true);
        $this->email = $user->data->user_email;
        $this->phone = get_user_meta($this->id, 'billing_phone', true);
        $this->street = get_user_meta($this->id, 'billing_address_1', true);
        $this->street_additional = get_user_meta($this->id, 'billing_address_2', true);
        $this->zip_code = get_user_meta($this->id, 'billing_postcode', true);
        $this->city = get_user_meta($this->id, 'billing_city', true);
        $this->company = get_user_meta($this->id, 'billing_company', true);
        $this->company_tax_id = get_user_meta($this->id, 'billing_company_tax_id', true);
        $this->website = get_user_meta($this->id, 'website', true);
        $this->status = get_user_meta($this->id, 'status', true);
    }

    public function to_array() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'job_title' => $this->job_title,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'street_additional' => $this->street_additional,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'company' => $this->company,
            'company_tax_id' => $this->company_tax_id,
            'website' => $this->website,
            'status' => $this->status
        ];
    }

    public function update_field($field, $value) {
        update_user_meta($this->id, $field, $value);
        $this->{$field} = $value;
    }

    public function found(){
        return !!$this->id;
    }
}