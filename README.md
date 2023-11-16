# radar-nfe
Buscar notas fiscais emitidas contra o CNPJ da empresa, serviço distribuição DFe [Nota Técnica 2014.002](https://www.nfe.fazenda.gov.br/portal/exibirArquivo.aspx?conteudo=C/xkRclIh74=)


## Funcionamento
Permitir que o usuário consiga buscar as notas emitidas contra o CNPJ de maneira simples.

* Sincronizar as notas, rodar a consulta de 3 em 3 horas
* Realizar manifestações
  * Ciência da operação
  * Desconhecimento da operação
  * Operação não realizada
  * Confirmação da operação

## Como executar
É necessário ter o docker e docker-compose instalados

~~~sh
cd docker/
docker-compose up -d --build
~~~

Acesse o [localhost](http://localhost)


## Pontos a pensar
- Inicialmente não vou buscar nenhum padrão vou separar as partes sem seguir nenhuma convenção, pois não conheço e usar por usar não é uma boa ideia
- Vou utilizar um componente para consumir o distribuição DFe e depois implemento na mão então tenho que deixar essa parte flexivel para fazer essa troca
- Não sei se será web ou cli, vou modelar e chamar na linha de comando quando estiver tudo 100% implemento uma interface
- Vou manter nos modelos a comunicação com o banco e depois penso se é a melhor abordagem
