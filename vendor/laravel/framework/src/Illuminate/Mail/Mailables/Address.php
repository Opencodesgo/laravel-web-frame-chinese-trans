<?php
/**
 * Illuminate，邮件，Mailables，地址 
 */

namespace Illuminate\Mail\Mailables;

class Address
{
    /**
     * The recipient's email address.
	 * 收件人电子邮件地址
     *
     * @var string
     */
    public $address;

    /**
     * The recipient's name.
	 * 收件人名字
     *
     * @var string|null
     */
    public $name;

    /**
     * Create a new address instance.
	 * 创建一个新的地址实例
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return void
     */
    public function __construct(string $address, string $name = null)
    {
        $this->address = $address;
        $this->name = $name;
    }
}
