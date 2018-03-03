el iva esta fijado a fuego...
   $chargeModel->setType('TAX')
      ->setAmount(new Currency(array(
        'value' => $data['payment_amount'] * .21,
        'currency' => $data['payment_currency']
      )));

      y encima siquiera se si lo estamos usando porque tengo esta linea comentada://    ->setSetupFee(new Currency(array('value' => 1, 'currency' => $entity->get('field_payment_currency')->value)));