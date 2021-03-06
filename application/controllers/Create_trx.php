<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Create_cart_trx extends API_Controller{
    /*
    
    */

    public function v10_post(){
        /*
        Version 1.0
        Date: 
        Features:
        - 

        Libraries:
        - Local (autoload)

        Helpers:
        - module_helper (autoload)

        Models:
        - trxdb (autoload)
        */
        $post = $this->post();
        $this->v10_validation($post);
        $data = $post['data'];
        unset($post);

        $this->db->trans_start();
        //get last cart_trx
        $trxid = $this->trxdb->get_last_trx_cartid($data['cart_id']);
        if(empty($trxid)){
            $trxid = 1;
        }
        else{
            $trxid = $trxid['trx_id']+1;
        }
        kibana_log('trx_user-create_trx', array('cart_id' => $data['cart_id'], 'trxid' => $trxid));

        //insert trx
        $params = array(
            "cart_id" => $data['cart_id'],
            "trx_id" => $trxid,
            "trx_user" => json_encode(array(
                "user_id" => $data['user_id'],
                "user_sisi" => 1
            )),
            "trx_tgl" => date_now('Y-m-d H:i:s'),
            "trx_kat" => json_encode(array(
                "trx_kategori" => $data['trx_kat']['trx_kategori'],
                "trx_subkategori" => $data['trx_kat']['trx_subkategori']
            )),
            "trx_status" => 1,
            "trx_substatus" => 1000
        );
        $trx = $this->trxdb->insert_trx($params);
        if($trx === false && is_bool($trx)){
            kibana_log('trx_user-error trx', array('params' => $params, 'note' => "insert failed"));
            false_response('message', 'insert trx failed', 'error.failed');
        }

        //insert log
        $params = array(
            "cart_id" => $data['cart_id'],
            "trx_id" => $trxid,
            "trxlog_tgl" => date_now('Y-m-d H:i:s'),
            "trxlog_isi" => json_encode(array()),
            "trxlog_status" => 1,
            "trxlog_substatus" => 1001
        );
        $log = $this->trxdb->insert_log($params);
        if($log === false && is_bool($log)){
            kibana_log('trx_user-error log', array('params' => $params, 'note' => "insert failed"));
            false_response('message', 'insert log failed', 'error.failed');
        }
        $this->db->trans_complete();

        succ_response(array("cart_id" => $data['cart_id'], 'trx_id' => $trxid));
    }

    private function v10_validation(&$post){
        /*
        Version 1.0
        Date: 
        Features:
        - Validasi request POST api versi 1.0
        */
        if(!empty($post['data'])){
            if(!isset($post['data']['user_id'])){
                false_response('invalid', 'user_id required', 'required', 'user_id');
            }
            else if(!check_num($post['data']['user_id'])){
                false_response('invalid', 'user_id is not integer', 'type', 'user_id');
            }
            if(empty($post['data']['cart_id'])){
                $post['data']['cart_id'] = 0;
            }
            else if(!check_num($post['data']['cart_id'])){
                false_response('invalid', 'cart_id is not integer', 'type', 'cart_id');
            }
            if(!isset($post['data']['user_id'])){
                false_response('invalid', 'user_id required', 'required', 'user_id');
            }
            if(empty($post['data']['trx_kat'])){
                false_response('invalid', 'trx_kat required', 'required', 'trx_kat');
            }
            else{
                if(empty($post['data']['trx_kat']['trx_kategori'])){
                    false_response('invalid', 'trx_kategori required', 'required', 'trx_kategori');
                }
                else if(!check_num($post['data']['trx_kat']['trx_kategori'])){
                    false_response('invalid', 'trx_kategori is not integer', 'type', 'trx_kategori');
                }
                if(empty($post['data']['trx_kat']['trx_subkategori'])){
                    false_response('invalid', 'trx_subkategori required', 'required', 'trx_subkategori');
                }
                else if(!check_num($post['data']['trx_kat']['trx_subkategori'])){
                    false_response('invalid', 'trx_subkategori is not integer', 'type', 'trx_subkategori');
                }
                if(empty($post['data']['trx_kat']['konten'])){
                    false_response('invalid', 'konten required', 'required', 'konten');
                }
                else if(!check_num($post['data']['trx_kat']['konten'])){
                    false_response('invalid', 'konten is not integer', 'type', 'konten');
                }
            }
            if(!empty($post['data']['detail'])){
                if(!is_array($post['data']['detail'])){
                    false_response('invalid', 'konten is not integer', 'type', 'detail');
                }
            }
        }
        else{
            false_response('invalid', 'data required', 'required', 'data');
        }
    }
}