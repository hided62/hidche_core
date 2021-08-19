
import axios from 'axios';
import jQuery from 'jquery';
import { Rules } from 'async-validator';
import { JQValidateForm } from './util/jqValidateForm';
import { convertFormData } from './util/convertFormData';
import { InvalidResponse } from './defs';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

jQuery(async function ($) {
    setAxiosXMLHttpRequest();

    const descriptor: Rules = {
        full_reset: {
            required: true,
        },
        db_host: {
            type: 'string',
            required: true,
        },
        db_port: {
            type: 'integer',
            transform: parseInt,
            validator: (rule, value: number) => {
                if (value <= 0 || value >= 65535) {
                    return new Error('올바른 포트 범위가 아닙니다.');
                }
                return true;
            }
        },
        db_id: {
            type: 'string',
            required: true,
        },
        db_pw: {
            type: 'string',
            required: true,
        },
        db_name: {
            type: 'string',
            required: true,
        }
    };
    const validator = new JQValidateForm($('#db_form'), descriptor);
    validator.installChangeHandler();
    $('#db_form').on('submit', async function (e) {
        e.preventDefault();

        const items = await validator.validate();
        if(items === undefined){
            return;
        }

        let data: InvalidResponse;

        try{
            const response = await axios({
                url: 'j_install_db.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData(items)
            })
            data = response.data as InvalidResponse;
        }
        catch(e){
            alert(e);
            return false;
        }
        if(!data.result){
            alert(`에러: ${data.reason}`);
            return false;
        }

        alert('DB.php가 생성되었습니다.');
        location.href = 'install.php';
    });
});