<?php
/**
 * @author Denis Fohl
 */

namespace Zac2\Common;


interface Renderable
{

    /**
     * @param  mixed|null $data
     * @return string
     */
    function render($data = null);

}