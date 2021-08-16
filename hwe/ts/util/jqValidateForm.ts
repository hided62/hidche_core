import Schema, { Rules, Values } from "async-validator";
import { isArray } from "lodash";
import { mergeKVArray } from "./mergeKVArray";
import $ from 'jquery';

export class JQValidateForm {
    public readonly validator: Schema;
    public readonly inputs: JQuery<HTMLElement>;
    constructor(public readonly target: JQuery<HTMLElement>, public readonly rule: Rules) {
        this.validator = new Schema(rule);
        this.inputs = target.find('input');
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

    public async validate(): Promise<undefined | Values> {
        const rawValues = mergeKVArray(this.inputs.serializeArray());
        const validateResult = await this.validator.validate(rawValues).catch(({ fields }) => {
            this.clearErrMsg();
            for(const key of Object.keys(fields)){
                let $item: JQuery<HTMLElement>;
                const errMsg = fields[key][0].message;
                if(isArray(rawValues[key])){
                    $item = $(`#db_form input[name='${key}[]']`);
                }
                else{
                    $item = $(`#db_form input[name='${key}']`);
                }
                $item.addClass('is-invalid');

                const $error = $(`<span>${errMsg}</span>`);

                $error.addClass( "invalid-feedback" );

                if ( $item.prop( "type" ) === "checkbox" ) {
                    $error.insertAfter( $item.parent( "label" ) );
                } else {
                    $error.insertAfter( $item );
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
        return validateResult;
    }
}