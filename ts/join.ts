import $ from 'jquery';
import axios from 'axios';
import { Rules } from 'async-validator';
import { JQValidateForm } from '../hwe/ts/util/jqValidateForm';
import { convertFormData } from '../hwe/ts/util/convertFormData';
import { InvalidResponse } from '../hwe/ts/defs';
import { setAxiosXMLHttpRequest } from '../hwe/ts/util/setAxiosXMLHttpRequest';
import { unwrap_any } from '../hwe/ts/util//unwrap_any';
import { sha512 } from 'js-sha512';
import { isString } from 'lodash';

$(async function () {
    setAxiosXMLHttpRequest();

    const terms1P = axios('../terms.1.html');
    const terms2P = axios('../terms.2.html');

    const descriptor: Rules = {
        username: {
            required: true,
            min: 4,
            max: 64,
            type: 'string',
            asyncValidator: async (rule, value: string) => {
                const response = await axios({
                    url: 'j_check_dup.php',
                    method: 'post',
                    responseType: 'json',
                    data: convertFormData({
                        type: 'username',
                        value: value,
                    })
                });
                const isValid: boolean|string = response.data;
                if (isString(isValid)) {
                    throw new Error(isValid);
                }
            },
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
            max: 9,
            asyncValidator: async (rule, value: string) => {
                const response = await axios({
                    url: 'j_check_dup.php',
                    method: 'post',
                    responseType: 'json',
                    data: convertFormData({
                        type: 'nickname',
                        value: value,
                    })
                });
                const isValid: boolean|string = response.data;
                if (isString(isValid)) {
                    throw new Error(isValid);
                }
            },
            options: {
                messages: {
                    required: "유저명을 입력해주세요",
                    string: {
                        max: (v, l) => `닉네임은 ${l}자를 넘을 수 없습니다`,
                    }
                }
            }
        },
        secret_agree: {
            required: true,
            transform: (v)=>v=='on',
            type: 'enum',
            enum: [true],
            message: '동의해야만 가입하실 수 있습니다.',
        },
        secret_agree2: {
            required: true,
            transform: (v)=>v=='on',
            type: 'enum',
            enum: [true],
            message: '동의해야만 가입하실 수 있습니다.',
        },
        third_use: {
            required: true,
            transform: (v)=>v=='on',
            type: 'boolean'
        }
    }
    const validator = new JQValidateForm($('#main_form'), descriptor);
    validator.installChangeHandler();

    $("#main_form").on('submit', async function (e) {
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
                url: 'j_join_process.php',
                responseType: 'json',
                method: 'post',
                data: convertFormData({
                    secret_agree: values.secret_agree,
                    secret_agree2: values.secret_agree2,
                    third_use: values.third_use,
                    username: values.username,
                    password: hash_pw,
                    nickname: values.nickname,
                })
            });
            result = response.data;
        }
        catch (e) {
            console.error(e);
            alert(`실패했습니다: ${e}`);
            return;
        }

        if (!result.result) {
            alert(result.reason);
            window.location.href = "../";
            return;
        }

        alert('회원 등록되었습니다.\n첫 로그인 과정에서 인증 코드를 입력하는 것으로 계정이 활성화됩니다.');
        window.location.href = "../";
    });

    $('#terms1').html((await terms1P).data);
    $('#terms2').html((await terms2P).data);
});