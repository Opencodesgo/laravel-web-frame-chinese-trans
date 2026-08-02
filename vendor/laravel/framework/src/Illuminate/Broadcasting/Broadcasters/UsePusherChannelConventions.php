<?php
/**
 * Illuminate，广播，广播员，广播员
 */

namespace Illuminate\Broadcasting\Broadcasters;

use Illuminate\Support\Str;

trait UsePusherChannelConventions
{
    /**
     * Return true if channel is protected by authentication.
	 * 如果信道受身份验证保护，则返回true
     *
     * @param  string  $channel
     * @return bool
     */
    public function isGuardedChannel($channel)
    {
        return Str::startsWith($channel, ['private-', 'presence-']);
    }

    /**
     * Remove prefix from channel name.
	 * 从信道名中删除前缀
     *
     * @param  string  $channel
     * @return string
     */
    public function normalizeChannelName($channel)
    {
        foreach (['private-encrypted-', 'private-', 'presence-'] as $prefix) {
            if (Str::startsWith($channel, $prefix)) {
                return Str::replaceFirst($prefix, '', $channel);
            }
        }

        return $channel;
    }
}
