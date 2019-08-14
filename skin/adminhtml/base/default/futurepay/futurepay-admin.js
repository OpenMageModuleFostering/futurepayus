var FuturePayAdmin = {
    
    loginTplEndpoint: null,
    signupTplEndpoint: null,
    signupLoginTplEndpoint: null,
    countryRegionsEndpoint: null,
    merchantLoginEndpoint: null,
    merchantSignupEndpoint: null,
    
    
    doMerchantSignupClick: function ()
    {
        this.displaySignupTpl();
    },
    
    doMerchantLoginClick: function ()
    {
        this.displayLoginTpl();
    },
    
    displayLoginTpl: function ()
    {
        new Ajax.Request(this.loginTplEndpoint, {
          method:'get',
          onSuccess: function(transport) {
            $$('#futurepay-merchant-login')
                    .invoke('update', transport.responseText);
            $('futurepay-merchant-signup').hide();
            $('futurepay-merchant-login').show();
          },
          onFailure: function() { /*alert('Something went wrong...');*/ }
        });
    },
    
    displaySignupTpl: function ()
    {
        new Ajax.Request(this.signupTplEndpoint, {
          method:'get',
          onSuccess: function(transport) {
            $$('#futurepay-merchant-signup')
                    .invoke('update', transport.responseText);
            $('futurepay-merchant-login').hide();
            $('futurepay-merchant-signup').show();
          },
          onFailure: function() { /*alert('Something went wrong...');*/ }
        });
    },
    
    displaySignupLoginTpl: function ()
    {
        new Ajax.Request(this.signupLoginTplEndpoint, {
          method:'get',
          onSuccess: function(transport) {
            $$('#futurepay-ajax-container').invoke('update', transport.responseText);
          },
          onFailure: function() { /*alert('Something went wrong...');*/ }
        });
    },
    
    refreshRegionsForCountry: function (el)
    {
        new Ajax.Request(this.countryRegionsEndpoint + '?country=' + el.value, {
            method: 'get',
            onSuccess: function (transport) {
                var resp = transport.responseJSON;
                
                if (resp.totalRecords > 0 && resp.items.length > 0) {
                    console.log($('futurepay-region-code').nodeName);
                    if ($('futurepay-region-code').nodeName == 'INPUT') {
                        $('futurepay-region-code').replace(
                            new Element('select', { id: 'futurepay-region-code', name: 'futurepay_region_code' })
                        );
                    }
                    
                    // remove old options
                    $('futurepay-region-code').select('option').each(function (child) {
                        child.remove();
                    });
                    
                    // for each, build new options
                    resp.items.each(function(region) {
                        var el = new Element('option', {
                            value: region.code
                        }).update(region.name);
                        $('futurepay-region-code').insert(el);
                    });
                    $('futurepay-region-code').enable();
                } else {
                    // disable the region field
                    //$('futurepay-region-code').disable();
                    $('futurepay-region-code').replace(
                        new Element('input', { id: 'futurepay-region-code', name: 'futurepay_region_code', class: 'fm-input' })
                    );
                }
            },
            onFailure: function () { /*alert('Something went wrong...');*/ }
        });
    },
    
    doMerchantLogin: function ()
    {
        if (this.validateMerchantLoginForm()) {
            new Ajax.Request(this.merchantLoginEndpoint, {
                method: 'post',
                parameters: {
                    // login and password
                    user_name: $('futurepay_user_name').value,
                    password: $('futurepay_password').value
                },
                onSuccess: function (transport) {                
                    if (typeof(transport.responseJSON) == 'object'
                            && transport.responseJSON.error == 0) {
                        $('payment_futurepay_gmid').value = transport.responseJSON.key;
                        $('futurepay-merchant-login').hide();
                    }
                    alert(transport.responseJSON.message);
                },
                onFailure: function () { /*alert('Something went wrong...');*/ }
            });
        }
    },
    
    validateMerchantLoginForm: function ()
    {
        if ($('futurepay_user_name').value.length < 1
                || $('futurepay_password').value.length < 1) {
            alert("The login and password fields can not be blank!");
            return false;
        } else {
            return true;
        }
    },
    
    doMerchantSignup: function ()
    {
        // get all the form names and values
        var data = {};
        $('futurepay-merchant-signup').select(':input[name!=futurepay_save]').each(function (el) {
            data[el.readAttribute('name')] = el.value;
        });
        
        // put the phone number together
        if (data.futurepay_main_phone.match(/[0-9]{10}/)) {
            data['futurepay_main_phone'] = data.futurepay_main_phone.replace(/([0-9]{3})([0-9]{3})([0-9]{4})/, "$1-$2-$3");
        }
        
        if (this.validateMerchantSignupForm(data)) {
            new Ajax.Request(this.merchantSignupEndpoint, {
                method: 'post',
                parameters: data,
                onSuccess: function (transport) {
                    if (typeof(transport.responseJSON) == 'object'
                            && transport.responseJSON.error == 0) {
                        $('payment_futurepay_gmid').value = transport.responseJSON.key;
                        $('futurepay-merchant-signup').hide();
                    }
                    alert(transport.responseJSON.message);
                    
                },
                onFailure: function () { /*alert('Something went wrong...');*/ }
            });
        }
        
    },
    
    validateMerchantSignupForm: function (data)
    {
        var valid = true;
        var messages = {};

        // check for blank fields
        var blanks = false;
        $H(data).each(function(pair) {
            if (pair.value.length < 1) {
                blanks = true;
                return;
            }
        });

        if (blanks) {
            alert("Failed to create a FuturePay account. All fields are required!");
            return false;
        }

        // contact email
        if (!data.futurepay_contact_email.match(/.+\@.+\..+/)) {
            messages.futurepay_contact_email = "The supplied email address is not valid.";
            valid = false;
        }

        // phone number
        if (!data.futurepay_main_phone.match(/[0-9]{3}-[0-9]{3}-[0-9]{4}/)
                && !data.futurepay_main_phone.match(/[0-9]{10}/)) {
            // try with ########## instead of ###-###-####
            messages.futurepay_main_phone = "The supplied phone number is not a valid 10-digit phone number.";
            valid = false;
        }

        // zip/postal
        if (data.futurepay_country_code == 'US' && !data.futurepay_zip.match(/[0-9]{5}/)) {
            messages.futurepay_zip = "The supplied ZIP code is not a valid ZIP code.";
            valid = false;
        } else if (data.futurepay_country_code == 'CA' && !data.futurepay_zip.match(/[A-Za-z0-9]{3}[- ]{0,1}[A-Za-z0-9]{3}/)) {
            messages.futurepay_zip = "The supplied postal code is not a valid postal code.";
            valid = false;
        }

        // the form is valid!
        if (valid) {
            return true;
        } else {
            // no it's not! spit out error messages
            var msg_string = 'Could not submit the merchant sign-up form. One or more fields contain incorrect data:\n\n';
            $H(messages).each(function(pair) {
                msg_string += pair.value + '\n';
            });
            
            alert(msg_string);
            return false;
        }
    }
};
