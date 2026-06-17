<?php

if (!function_exists('reda_money_format')) {
    /**
     * Formatea un valor monetario con separador de miles (.) y decimales (,)
     * @param string $symbol Símbolo de la moneda
     * @param float $value Valor numérico
     * @return string
     */
    function reda_money_format($symbol, $value)
    {
        $formattedValue = number_format($value, 2, ',', '.');
        $symbolPosition = reda_currency_symbol_position();
        
        if ($symbolPosition == "before") {
            return $symbol . ' ' . $formattedValue;
        } else {
            return $formattedValue . ' ' . $symbol;
        }
    }
}

if (!function_exists('reda_number_format')) {
    /**
     * Formatea un número con separador de miles (.) y decimales (,)
     * @param float $number
     * @param int $decimal
     * @return string
     */
    function reda_number_format($number, $decimal)
    {
        return number_format($number, $decimal, ',', '.');
    }
}

if (!function_exists('reda_currency_symbol_position')) {
    /**
     * Obtiene la posición del símbolo de moneda desde la configuración
     * @return string
     */
    function reda_currency_symbol_position()
    {
        $position = settings('money_format');
        return !empty($position) ? $position : 'after';
    }
}
