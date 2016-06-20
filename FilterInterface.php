<?php
/**
 * @author Denis Fohl
 */

namespace Zac2\Common;


interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception If filtering $value is impossible
     * @return mixed
     */
    public function filter($value);
}