import $ from 'jquery';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { InvalidResponse } from '@/defs';
import { convertFormData } from '@util/convertFormData';
import { unwrap_any } from '@util/unwrap_any';

$(function($){
    setAxiosXMLHttpRequest();

    $('.submitBtn').on('click', async function(e){
        e.preventDefault();

        const $this = $(this);
        const target = parseInt($this.data('target'));
        const amount = parseInt(unwrap_any<string>($(`#target_${target}`).val()));

        let result: InvalidResponse;

        try{
            const response = await axios({
                url: 'j_betting.php',
                responseType: 'json',
                method: 'post',
                data: convertFormData({
                    target: target,
                    amount: amount
                })
            });
            result = response.data;
        }catch(e){
            console.error(e);
            alert(`에러: ${e}`);
            location.reload();
            return;
        }

        if(!result.result){
            alert(`베팅을 실패했습니다: ${result.reason}`);
            location.reload();
            return;
        }

        location.reload();
        return;
    });
});