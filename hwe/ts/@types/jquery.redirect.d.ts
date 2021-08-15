type JqueryRedirectOpts = {
    url: string,
    values?: Record<string, string | number | string[] | boolean>,
    method?: 'GET' | 'POST',
    target?: string,
    traditional?: boolean
    redirectTop?: boolean,
}

interface JQueryStatic {
    /**
    * jQuery Redirect
    * @param {string} url - Url of the redirection
    * @param {Object} values - (optional) An object with the data to send. If not present will look for values as QueryString in the target url.
    * @param {string} method - (optional) The HTTP verb can be GET or POST (defaults to POST)
    * @param {string} target - (optional) The target of the form. If you set "_blank" will open the url in a new window.
    * @param {boolean} traditional - (optional) This provides the same function as jquery's ajax function. The brackets are omitted on the field name if its an array.  This allows arrays to work with MVC.net among others.
    * @param {boolean} redirectTop - (optional) If its called from a iframe, force to navigate the top window. 
    */
    redirect(
        url: string,
        values?: Record<string, string | number | string[] | boolean>,
        method?: 'GET' | 'POST',
        target?: string, traditional?:
            boolean, redirectTop?: boolean
    ): void;

    /**
* jQuery Redirect
* @param {string} opts - Options object
* @param {string} opts.url - Url of the redirection
* @param {Object} opts.values - (optional) An object with the data to send. If not present will look for values as QueryString in the target url.
* @param {string} opts.method - (optional) The HTTP verb can be GET or POST (defaults to POST)
* @param {string} opts.target - (optional) The target of the form. "_blank" will open the url in a new window.
* @param {boolean} opts.traditional - (optional) This provides the same function as jquery's ajax function. The brackets are omitted on the field name if its an array.  This allows arrays to work with MVC.net among others.
* @param {boolean} opts.redirectTop - (optional) If its called from a iframe, force to navigate the top window. 
*/
    redirect(opt: JqueryRedirectOpts): void;
}