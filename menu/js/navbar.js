$('#id_sair').bind({click: sairSistema})
function sairSistema(){
    $('#id_multi p').text('Deseja sair do sistema ?')
    $('#id_multi button').eq(1).bind({click: ()=>{
        location.href="../api/logoff.php"
    }})
    $('#id_multi').css({display: 'flex'})
}