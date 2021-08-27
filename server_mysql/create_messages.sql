create table messages (
    id int not null auto_increment,
    msg_key VARCHAR(30) NOT NULL,
    msg VARCHAR(255),
    PRIMARY KEY (id)
);
