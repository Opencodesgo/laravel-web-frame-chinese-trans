<?php
/**
 * Illuminate，契约，Mail，可连接的
 */

namespace Illuminate\Contracts\Mail;

interface Attachable
{
    /**
     * Get an attachment instance for this entity.
	 * 获取此实体的附件实例
     *
     * @return \Illuminate\Mail\Attachment
     */
    public function toMailAttachment();
}
