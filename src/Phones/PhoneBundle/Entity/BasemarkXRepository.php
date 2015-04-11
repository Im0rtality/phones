<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BasemarkXRepository extends EntityRepository
{
    /**
     * @param BasemarkX $stat
     */
    public function save(BasemarkX $stat)
    {
        $em = $this->getEntityManager();

        $phone = $em->getRepository('PhonesPhoneBundle:Phone')->find($stat->getPhoneId());
        if ($phone) {
            $stat->setPhone($phone);

            $criteria = [
                'phoneId'     => $stat->getPhoneId(),
                'provider_id' => $stat->getProviderId(),
            ];

            $entityRez = $this->findBy($criteria);

            if (empty($entityRez)) {
                $em->persist($stat);
                $em->flush($stat);
            }
        }
    }
}
