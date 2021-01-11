$('#id_alerta').bind({click:escorderQuadro})
function escorderQuadro(){
    $(this).closest('section').css({display: 'none'})
}