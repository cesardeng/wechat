<?php

/**
 * Notify.php.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 *
 * @link      https://github.com/overtrue
 * @link      http://overtrue.me
 */
namespace EasyWeChat\Payment;

use EasyWeChat\Core\Exceptions\FaultException;
use EasyWeChat\Support\Collection;
use EasyWeChat\Support\XML;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Notify.
 */
class Notify
{
    /**
     * Merchant instance.
     *
     * @var \EasyWeChat\Payment\Merchant
     */
    protected $merchant;

    /**
     * Request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Payment notify (extract from XML).
     *
     * @var Collection
     */
    protected $notify;

    /**
     * Constructor.
     *
     * @param Merchant $merchant
     * @param Request  $request
     */
    public function __construct(Merchant $merchant, Request $request = null)
    {
        $this->merchant = $merchant;
        $this->request = $request ?: Request::createFromGlobals();
    }

    /**
     * Validate the request params.
     *
     * @return bool
     */
    public function isValid()
    {
        $localSign = generate_sign($this->getNotify()->except('sign')->all(), $this->merchant->key, 'md5');

        return $localSign === $this->getNotify()->sign;
    }

    /**
     * Return the notify body from request.
     *
     * @return \EasyWeChat\Support\Collection
     */
    public function getNotify()
    {
        if (!empty($this->notify)) {
            return $this->notify;
        }
        try {
            $xml = XML::parse($this->request->getContent());
        } catch (\Throwable $t) {
            throw new FaultException('Invalid request XML: '.$t->getMessage(), 400);
        } catch (\Exception $e) {
            throw new FaultException('Invalid request XML: '.$e->getMessage(), 400);
        }

        if (!is_array($xml) || empty($xml)) {
            throw new FaultException('Invalid request XML.', 400);
        }

        return $this->notify = new Collection($xml);
    }
}
