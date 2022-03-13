
import $ from 'jquery';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { InvalidResponse } from '@/defs';
import { JQValidateForm, NamedRules } from '@util/jqValidateForm';
import { convertFormData } from '@util/convertFormData';
import { unwrap_any } from '@util/unwrap_any';
import { mb_strwidth } from '@util/mb_strwidth';
import { isString } from 'lodash';
import { sha512 } from 'js-sha512';
import '@/gateway/common';

async function changeInstallMode() {
    let result: {
        step: string,
        globalSalt: string,
    };

    try {
        const response = await axios({
            url: 'j_install_status.php',
            method: 'post',
            responseType: 'json'
        });
        result = response.data;
    } catch (e) {
        console.error(e);
        alert(`에러: ${e}`);
        return;
    }
    if (result.step == 'config') {
        $('#db_form_card').show();
        $('#admin_form_card').hide();
        return;
    }
    if (result.step == 'admin') {
        $('#db_form_card').hide();
        $('#admin_form_card').show();
        $('#global_salt').val(result.globalSalt);
        return;
    }
    if (result.step == 'done') {
        alert('설치가 완료되었습니다.');
        window.location.href = "..";
        return;
    }
    if (result.step == 'conn_fail') {
        $('#db_form_card').hide();
        $('#admin_form_card').hide();
        alert('설치 이후 DB 설정이 변경된 것 같습니다. RootDB.php 파일의 설정을 확인해주십시오.');
        return;
    }
    if (result.step == 'sql_fail') {
        $('#db_form_card').hide();
        $('#admin_form_card').hide();
        alert('DB가 제대로 설정되지 않았거나, 훼손된 것 같습니다. DB를 복구하거나 RootDB.php 파일을 삭제 후 재설치를 진행해 주십시오.');
        return;
    }

    alert(`알 수 없는 오류: ${result}`);
}

function setupDBForm() {
    const parentPathname = location.pathname.split('/').slice(0, -2).join('/');
    $('#serv_host').val(
        [location.protocol, '//', location.host, parentPathname].join('')
    );

    $('#btn_random_generate_key').on('click', function (e) {
        e.preventDefault();
        let token = '';
        while (token.length < 24) {
            token += (Math.random() + 1).toString(36).substring(7);
        }
        token = token.substr(0, 24);
        $('#image_request_key').val(token);
    });

    type DBFormType = {
        db_host: string,
        db_port: string,
        db_id: string,
        db_pw: string,
        db_name: string,
        serv_host: string,
        shared_icon_path: string,
        game_image_path: string,
        image_request_key: string,
        kakao_rest_key: string,
        kakao_admin_key: string,
    }
    const descriptor: NamedRules<DBFormType> = {
        db_host: {
            required: true,
            type: 'string',
        },
        db_port: {
            required: true,
            type: 'number',
            transform: parseInt,
        },
        db_id: {
            required: true,
            type: 'string',
        },
        db_pw: {
            required: true,
            type: 'string',
        },
        db_name: {
            required: true,
            type: 'string',
        },
        serv_host: {
            required: true,
            type: 'string',
        },
        shared_icon_path: {
            required: true,
            type: 'string',
        },
        game_image_path: {
            required: true,
            type: 'string',
        },
        image_request_key: {
            required: false,
            type: 'string',
            min: 16,
        },
        kakao_rest_key: {
            required: false,
            type: 'string',
        },
        kakao_admin_key: {
            required: false,
            type: 'string',
        }
    }
    const validator = new JQValidateForm($('#db_form'), descriptor);
    validator.installChangeHandler();

    $('#db_form').on('submit', async function (e) {
        e.preventDefault();
        const values = await validator.validate();
        if (values === undefined) {
            return;
        }

        let result: InvalidResponse;
        try {
            const response = await axios({
                url: 'j_setup_db.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    db_host: values.db_host,
                    db_port: values.db_port,
                    db_id: values.db_id,
                    db_pw: values.db_pw,
                    db_name: values.db_name,
                    serv_host: values.serv_host,
                    shared_icon_path: values.shared_icon_path,
                    game_image_path: values.game_image_path,
                    image_request_key: values.image_request_key,
                    kakao_rest_key: values.kakao_rest_key,
                    kakao_admin_key: values.kakao_admin_key,
                })
            });
            result = response.data;
        } catch (e) {
            console.error(e);
            alert(`에러: ${e}`);
            return;
        }

        if (!result.result) {
            alert(result.reason);
            return;
        }

        alert('RootDB.php가 생성되었습니다. 관리자 계정 생성을 진행합니다.');
        await changeInstallMode();
    });
}

function maxStrWidth(maxWidth: number) {
    return (rule: unknown, value: string) => {
        if (!value || !isString(value) || mb_strwidth(value) > maxWidth) {
            return new Error(`글자 너비가 알파벳 ${maxWidth} 자보다 길지 않아야합니다`);
        }
        return true;
    };
}

function setupAdminForm() {
    type AdminValues = {
        username: string,
        password: string,
        confirm_password: string,
        nickname: string,
    }
    const descriptor: NamedRules<AdminValues> = {
        username: {
            required: true,
            min: 4,
            max: 64,
            type: 'string',
            options: {//FIXME: options.messages가 동작하지 않는다?
                messages: {
                    required: "유저명을 입력해주세요",
                    string: {
                        min: (v, l) => `${l}글자 이상 입력하셔야 합니다`,
                        max: (v, l) => `${l}자를 넘을 수 없습니다`,
                    }
                }
            }
        },
        password: {
            required: true,
            type: 'string',
            min: 6,
            options: {
                messages: {
                    required: "비밀번호를 입력해주세요",
                    string: {
                        min: (v, l) => `비밀번호는 적어도 ${l}글자 이상이어야 합니다`
                    }
                }
            }
        },
        confirm_password: {
            required: true,
            type: 'string',
            min: 6,
            validator: (rule, value: string, _callback, source) => {
                console.log(value);
                if (value != (source.password ?? '')) {
                    return new Error('비밀번호가 일치하지 않습니다');
                }
                return true;
            },
            options: {
                messages: {
                    required: "비밀번호를 입력해주세요",
                    string: {
                        min: (v, l) => `비밀번호는 적어도 ${l}글자 이상이어야 합니다`
                    }
                }
            }
        },
        nickname: {
            required: true,
            validator: maxStrWidth(18),
            options: {
                messages: {
                    required: "유저명을 입력해주세요",
                }
            }
        },
    }
    const validator = new JQValidateForm($('#admin_form'), descriptor);
    validator.installChangeHandler();

    $('#admin_form').on('submit', async function (e) {
        e.preventDefault();
        const values = await validator.validate();
        if (values === undefined) {
            return;
        }

        const raw_password = values.password;
        const salt = unwrap_any<string>($('#global_salt').val());
        console.log(salt + raw_password + salt);
        const hash_pw = sha512(salt + raw_password + salt);

        let result: InvalidResponse;
        try {
            const response = await axios({
                url: 'j_create_admin.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    username: values.username,
                    password: hash_pw,
                    nickname: values.nickname,
                })
            });
            result = response.data;
        } catch (e) {
            console.error(e);
            alert(`에러: ${e}`);
            return;
        }

        if (!result.result) {
            alert(result.reason);
            return;
        }

        alert('관리자 계정이 생성되었습니다.');
        await changeInstallMode();
    });
}

$(function () {
    setAxiosXMLHttpRequest();

    void changeInstallMode();
    setupDBForm();
    setupAdminForm();



});