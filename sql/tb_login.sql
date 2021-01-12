create table tb_login(
    id_login    bigint constraint pk_login primary key auto_increment,
    login       varchar(30) not null,
    nome        varchar(120) not null,
    status      enum('A','D') comment is 'A.:Ativo - D.:Desabilitado' not null,
    password,    varchar(100),
    nivel       enum('1','2') comment '1.:Administrador-2.:Operador'
);

create table au_login(
    id_auditoria    bigint constraint pk_au_login primary key auto_increment,
    id_login        bigint,
    login           varchar(30) not null,
    nome            varchar(120) not null,
    status          enum('A','D') comment is 'A.:Ativo - D.:Desabilitado' not null,
    password        varchar(100),
    nivel       enum('1','2') comment '1.:Administrador-2.:Operador'
    tipo            enum('I','U','D') not null,
    operador        varchar(30) not null,
    data_auditoria  timestamp
);

create index ix_login$tb_login on tb_login.id_login;

delimiter !!

create or replace trigger tg_login_i
after insert on tb_login
for each row
begin
    insert into au_login
    (id_auditoria,id_login,login,nome,status,password,nivel,tipo,operador,data_auditoria)
    values
    (null,new.id_login,new.login,new.nome,new.status,new.password,new.nivel,'I',user(),now());
end!!

create or replace trigger tg_login_u
after update on tb_login
for each row
begin
    declare $login           varchar(30)     default null; 
    declare $nome            varchar(120)    default null; 
    declare $status          enum('A','D')   default null; 
    declare $password        varchar(100)    default null;
    declare $nivel           enum('1','2')   default null;

    if new.login    != old.login    then set $login = old.login;         end if;
    if new.nome     != old.nome     then set $nome = old.nome;           end if;
    if new.status   != old.status   then set $status = old.status;       end if;
    if new.password != old.password then set $password = old.password;   end if;
    if new.nivel != old.nivel then set $nivel = old.nivel;   end if;
    
    insert into au_login
    (id_auditoria,id_login,login,nome,status,password,nivel,tipo,operador,data_auditoria)
    values
    (null,$id_login,$login,$nome,$status,$password,$nivel,'U',user(),now());
end!!

create or replace trigger tg_login_d
after delete on tb_login
for each row
begin
    insert into au_login
    (id_auditoria,id_login,login,nome,status,password,nivel,tipo,operador,data_auditoria)
    values
    (null,old.id_login,old.login,old.nome,old.status,old.password,old.nivel,'D',user(),now());
end!!

delimiter ;

insert into tb_login
(id_login,login,status,password,nivel)
values
(null,'root','A',null);

insert into tb_login
(id_login,login,status,password,nivel)
values
(null,'jailton','A',null,2);