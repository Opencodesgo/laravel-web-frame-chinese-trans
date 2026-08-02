<?php
/**
 * Illuminate，队列，可序列化的闭包
 */

namespace Illuminate\Queue;

use Opis\Closure\SerializableClosure as OpisSerializableClosure;

class SerializableClosure extends OpisSerializableClosure
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * Transform the use variables before serialization.
	 * 在序列化之前转换use变量
     *
     * @param  array  $data  The Closure's use variables
     * @return array
     */
    protected function transformUseVariables($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->getSerializedPropertyValue($value);
        }

        return $data;
    }

    /**
     * Resolve the use variables after unserialization.
	 * 解析反序列化后的use变量
     *
     * @param  array  $data  The Closure's transformed use variables
     * @return array
     */
    protected function resolveUseVariables($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->getRestoredPropertyValue($value);
        }

        return $data;
    }
}
