import Schema, { type Rule, type Values } from "async-validator";
import { isArray } from "lodash-es";
import { mergeKVArray } from "@util/mergeKVArray";
import $ from 'jquery';

type Option = {
    preParse?: ($target?: JQuery<HTMLElement>)=>void,
    postParse?: (values:Record<string, string | string[]>, $target?: JQuery<HTMLElement>)=>[Record<string, string | string[]>, Map<string, string>]
}

type DefaultFormDataType = Record<string, null|number[]|string[]|boolean[]|number|string|boolean>;

export type NamedRules<T extends Record<string, unknown>> = {
    [v in keyof T]: Rule
}

export class JQValidateForm<TypedValue extends Values = DefaultFormDataType> {

    public readonly validator: Schema;
    public readonly inputs: JQuery<HTMLElement>;
    constructor(public readonly target: JQuery<HTMLElement>, public readonly rule: NamedRules<TypedValue>, public readonly option?:Option) {
        this.validator = new Schema(rule);
        this.inputs = target.find(':input');
    }

    public clearErrMsg():void {
        this.inputs.removeClass('is-invalid');
        this.inputs.removeClass('is-valid');
        this.target.find('.invalid-feedback').detach();
    }

    public installChangeHandler():this{
        this.inputs.on('change', ()=>{
            void this.validate();
        });
        return this;
    }

    public async validate(): Promise<undefined | TypedValue> {
        if(this.option?.preParse !== undefined){
            this.option.preParse(this.target);
        }
        let rawValues = mergeKVArray(this.inputs.serializeArray());
        let optMap: Map<string, string>;
        if(this.option?.postParse !== undefined){
            [rawValues, optMap] = this.option.postParse(rawValues, this.target);
        }
        else{
            optMap = new Map();
        }
        console.log(rawValues);

        const validateResult = await this.validator.validate(rawValues).catch(({fields }) => {
            if(fields === undefined){
                console.error('validator 에러, 조건 검사 구문을 확인하세요.');
                return;
            }
            this.clearErrMsg();
            for(const rawKey of Object.keys(fields)){
                let $item: JQuery<HTMLElement>;
                const key = rawKey.split('.')[0];
                console.log(`ErrorType: ${key}:${rawValues[key]}`);
                const errMsg = fields[rawKey][0].message;

                if(optMap.has(key)){
                    $item = this.target.find(optMap.get(key) as string);
                }
                else if(isArray(rawValues[key])){
                    $item = this.target.find(`:input[name='${key}[]']`);
                }
                else{
                    $item = this.target.find(`:input[name='${key}']`);
                }

                if($item.length == 0){
                    continue;
                }

                const $error = $(`<span>${errMsg}</span>`);

                $error.addClass( "invalid-feedback" );

                if ("radio" == $item.prop( "type" )) {
                    const $target = $item.parents( ".btn-group" );
                    $error.insertAfter( $target );
                    $target.addClass('is-invalid');
                }
                else if ("checkbox" == $item.prop( "type" )) {
                    let $target = $item.parent( "label" );
                    if($target.parent(".btn-group").length){
                       $target = $target.parent('.btn-group');
                    }
                    $error.insertAfter( $target );
                    $target.addClass('is-invalid');
                } else {
                    $error.insertAfter( $item );
                    $item.addClass('is-invalid');

                }
            }

            this.inputs.each(function(){
                const name = (this as HTMLInputElement).name;
                if(name in fields){
                    return;
                }
                this.classList.add('is-valid');
            });
            return undefined;
        });
        if(validateResult === undefined){
            return undefined;
        }
        this.clearErrMsg();
        this.inputs.addClass('is-valid');
        return validateResult as TypedValue;
    }
}