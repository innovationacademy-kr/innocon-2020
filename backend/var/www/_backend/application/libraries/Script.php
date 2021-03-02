<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Script
{

    protected $CI;
    protected $login_url = '/auth/login';

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function define($func, $call = [])
    {
        $this->CI->output->set_header('Content-type: text/javascript; charset=utf-8');

        // //첫번째를 알 필요가 있다.
        $form = $this->form();

        $script = <<<EOT
if(typeof $func == "undefined"){
    var $func = {
        form: $form
        ,query2obj: function(){
            var search = location.search.substring(1);
            if(search.length > 0) {
                return JSON.parse('{"' + decodeURI(search).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}')
            }
            else {
                return {};
            }
        }
        ,obj2query: function(obj, prefix) {
            var str = [], k, v;
            for(var p in obj) {
                if (!obj.hasOwnProperty(p)) {continue;} // skip things from the prototype
                if (~p.indexOf('[')) {
                    k = prefix ? prefix + "[" + p.substring(0, p.indexOf('[')) + "]" + p.substring(p.indexOf('[')) : p;
                } else {
                    k = prefix ? prefix + "[" + p + "]" : p;
                }
                v = obj[p];
                str.push(typeof v == "object" ?
                toQueryString(v, k) :
                encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
            return str.join("&");
        }
EOT;
        foreach ($call as $idx => $fun) {
            $script .= <<<EOT
            
        ,$idx: $fun
EOT;
        }
        $script .= <<<EOT
        
    };
}
EOT;
        return $script;
    }

    public function post($href, $header = [])
    {
        //         foreach ($header as $idx => $val) {
        //                 "$idx": html_escape($val)
        // }
        $script = <<<EOT
function(query, form, callback){
            fetch("/api/$href?" + Object.keys(query).map(function (key) { return encodeURIComponent(key) + "=" + encodeURIComponent(query[key]);}).join("&"), {
                method: "POST",
                body: JSON.stringify(form),
                cache: 'no-cache',
                credentials: "include",
                redirect: 'manual',
                headers: {
                    "Content-Type": "application/json"
                    ,"X-Requested-With": "XMLHttpRequest"
EOT;
        foreach ($header as $idx => $val) {
            $script .= <<<EOT

                        ,"$idx": $val
EOT;
        }
        $script .= <<<EOT

                    },
                })
                .then(function(res){
                    return res.json();
                })
                .then(function(json) {
                    switch (json.status) {
                        case 200:
                            if (typeof callback == "function") {
                                callback(json.body);
                            }
                        break;
                        /*
                        case 401:
                            localStorage.removeItem('token');
                            window.location.href = "$this->login_url?prev=" + escape(window.location.pathname);
                        break;
                        */
                        case 404:
                            var path = window.location.pathname;
                            window.location.href = path.substring(0, path.lastIndexOf('/'));
                            var msg = Object.values(json.msg).join("\\n");
                            alert(msg);
                        break;
                        default:
                            var msg = Object.values(json.msg).join("\\n");
                            alert(msg);
                        break;
                    }
                });
            }
EOT;

        return $script;
    }


    public function get($href, $header = [])
    {
        //         foreach ($header as $idx => $val) {
        //                 "$idx": html_escape($val)"
        // }
        $script = <<<EOT
function(form, callback){
            fetch("/api/$href?" + Object.keys(form).map(function (key) { return encodeURIComponent(key) + "=" + encodeURIComponent(form[key]);}).join("&"), {
                headers: {
                    "Content-Type": "application/json"
                    ,"X-Requested-With": "XMLHttpRequest"
EOT;

        foreach ($header as $idx => $val) {
            $script .= <<<EOT

                    ,"$idx": $val
EOT;
        }
        $script .= <<<EOT

                },
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(json) {
                switch (json.status) {
                    case 200:
                        if (typeof callback == "function") {
                            callback(json.body);
                        }
                    break;
                    /*
                    case 401:
                        window.location.href = "$this->login_url?prev=" + escape(window.location.pathname);
                    break;
                    */
                    case 404:
                        var path = window.location.pathname;
                        window.location.href = path.substring(0, path.lastIndexOf('/'));
                        var msg = Object.values(json.msg).join("\\n");
                        alert(msg);
                    break;
                    default:
                        var msg = Object.values(json.msg).join("\\n");
                        alert(msg);
                    break;
                }
            });
        }
EOT;

        return $script;
    }

    public function form_()
    {

        $script = <<<EOT
function(f, callback){
            f.addEventListener('submit', function(e) {
                e.preventDefault();
                var obj = {};
                var regex = /([a-zA-Z0-9_]+)(\[([a-zA-Z0-9_]+)\])*?/gm;
                var f = new FormData(e.target);
                var entries = f.entries();
                for (var k in entries) {
                    if (entries.hasOwnProperty(k)) {
                        var row = entries[k];
                        var dump = {};
                        var root;
                        var matches = row[0].match(regex);
                        for (var i in matches) {
                            if (matches.hasOwnProperty(i)) {
                                var idx = matches[i];
                                if (Object.keys(matches).length == 1) {
                                    obj[idx] = row[1];
                                }
                                else {
                                    if (Number(i) == 0) {
                                        if (typeof obj[idx] == 'undefined') {
                                            obj[idx] = {};
                                        }
                                        dump = obj[idx];
                                    }
                                    else if (Number(i) == Object.keys(matches).length - 1) {
                                        dump[idx] = row[1];
                                    }
                                    else {
                                        dump[idx] = {};
                                        dump = dump[idx];
                                    }
                                }
                            }
                        }
                    }
                }
                if(typeof callback == 'function'){
                    callback(obj);
                }
            });
        }
EOT;
        return $script;
    }
    public function form()
    {

        $script = <<<EOT
function(f, callback){
            f.addEventListener('submit', function(e){
                e.preventDefault();

                var obj = {};
                var regex = /[a-z0-9A-Z\/ _-가-힝]+/gm;

                var form = e.target;
                if (typeof form == 'object' && form.nodeName == "FORM") {
                    for(var k in form.elements) {
                        if (form.elements.hasOwnProperty(k)) {
                            var field = form.elements[k];
                            switch(field.type) {
                                case 'button':
                                case 'submit':
                                break;
                                case 'checkbox':
                                case 'radio':
                                    var m;
                                    var matches = [];
                                    if(field.checked && field.value) {
                                        while ((m = regex.exec(field.name)) !== null) {
                                            if (m.index === regex.lastIndex) {
                                                regex.lastIndex++;
                                            }
                                            matches.push(m[0]);
                                        }
                                        if(matches.length == 1) {
                                            obj[field.name] = field.value;
                                        }
                                        else {
                                            for(var i = 1; i <= matches.length; i++) {
                                                if(typeof eval("obj['" + matches.slice(0,i).join("']['") + "']") == 'undefined') {
                                                    if(matches.length > i) {
                                                        eval("obj['" + matches.slice(0,i).join("']['") + "'] = {};");
                                                    }
                                                    else {
                                                        eval("obj['" + matches.slice(0,i).join("']['") + "'] = [];");
                                                    }
                                                }
                                                if(matches.length == i) {
                                                    eval("obj['" + matches.slice(0,i).join("']['") + "'].push('"+field.value+"');");
                                                }
                                            } 
                                        }
                                    }
                                break;
                                case 'email':
                                case 'text':
                                case 'password':
                                case 'number':
                                case 'textarea':
                                    var m;
                                    var matches = [];
                                    if(field.value) {
                                        while ((m = regex.exec(field.name)) !== null) {
                                            if (m.index === regex.lastIndex) {
                                                regex.lastIndex++;
                                            }
                                            matches.push(m[0]);
                                        }

                                        if(matches.length == 1) {
                                            obj[field.name] = field.value;
                                        }
                                        else {
                                            for(var i = 1; i <= matches.length; i++) {
                                                if(typeof eval("obj['" + matches.slice(0,i).join("']['") + "']") == 'undefined') {
                                                    if(matches.length > i) {
                                                        eval("obj['" + matches.slice(0,i).join("']['") + "'] = {};");
                                                    }
                                                    else {
                                                        eval("obj['" + matches.slice(0,i).join("']['") + "'] = '';");
                                                    }
                                                }
                                                if(matches.length == i) {
                                                    eval("obj['" + matches.slice(0,i).join("']['") + "'] = '"+field.value.trim()+"';");
                                                }
                                            } 
                                        }
                                    }
                                break;
                                
                                default:
                                    for(var x = 0; x < field.length; x++) {
                                        if(field[x].checked === true && field[x].value.trim() != '') {
                                            if(field.length > 0) {
                                                var m;
                                                var matches = [];
                                                while ((m = regex.exec(field[x].name)) !== null) {
                                                    if (m.index === regex.lastIndex) {
                                                        regex.lastIndex++;
                                                    }
                                                    matches.push(m[0]);
                                                }
                                                if(matches.length == 1) {
                                                    obj[field[x].name] = field[x].value;
                                                }
                                                else {
                                                    for(var i = 1; i <= matches.length; i++) {
                                                        if(typeof eval("obj['" + matches.slice(0,i).join("']['") + "']") == 'undefined') {
                                                            if(matches.length > i) {
                                                                eval("obj['" + matches.slice(0,i).join("']['") + "'] = {};");
                                                            }
                                                            else {
                                                                eval("obj['" + matches.slice(0,i).join("']['") + "'] = [];");
                                                            }
                                                        }
                                                        if(matches.length == i) {
                                                            eval("obj['" + matches.slice(0,i).join("']['") + "'].push('"+field[x].value+"');");
                                                        }
                                                    } 
                                                }
                                            }
                                        }
                                    }
                                break;
                            }
                        }
                    }
                }
                if(typeof callback == 'function'){
                    callback(obj);
                }
            });
        }
EOT;
        return $script;
    }
}
