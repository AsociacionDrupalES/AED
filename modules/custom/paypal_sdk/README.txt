TODO:
- falta una pantalla en al cual se pueda poner la apiy key y el secret. Ahora mismo los tengo hardcoded en paypal_sdk.services.yml
- Cuando se ponga la apikey de paypal deberiamos recuperar todos los planes disponibles y crear las entidades correspondientes? lo mismo para los agreements
- actualizar un plan cuando la entidad se actualiza.
- Eliminar todos los agreements cuando una cuenta de usuario se elimina.
- Cache paypal link generation.
- limitar la vista para que solo se vean los agreements de cada persona.
en el composer de drupal necesito         "paypal/rest-api-sdk-php": "^1.7" por lo que hay que ver como un modulo puede usar su composer.json
- a futuro deberiamso permitir agreements por credit card tb.
- cuando se cree un plan que vuelva al listado de planes.
- el field subscription da una opcion para definir si es single o multivalued y en realidad nuca vamos a soportar multivalued... o si. IDEA: si es multivalued un usuario puede marcar varios planes para que se rendericen. En plan "susc anual", "susc mensual", "susc por días". revisar el code y adaptarlo para que soporte multivalued.
- Acabar de deprecar las entidades y usar solo los planes y agreemens remotos.
- poner limites a los elementos de form de plan y agreement.