$('#id_alerta').bind({click:escorderQuadro})
function escorderQuadro(){
    $(this).closest('section').css({display: 'none'})
}
//===========================================================
//ocultar bloco==============================================
//===========================================================
$('#id_multi button').eq(0).bind({click: function(){
           $(this).closest('#id_multi').css({display: 'none'})                  
}})