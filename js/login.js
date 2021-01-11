//================================================================
//Validar login===================================================
//================================================================
$('#id_formulariologin').bind({submit: validarLogin})
//================================================================
function validarLogin(form){
    form.preventDefault()
    $.ajax({
        data: $('#id_formulariologin').serialize(),
        type: 'post',
        url: 'api/login.php',
        dataType: 'json',
        success: (retorno)=>{
            if(retorno.error){
                $('#id_alerta p').text(retorno.msg)
                $('#id_alerta').css({display: 'flex'})
            }
            else{
                
            }
        },
        error: (retorno)=>{
            $('#id_alerta p').text('Houve um erro ao solicitar login-COD:001FE')
            $('#id_alerta').css({display: 'flex'})
        }
    })
}