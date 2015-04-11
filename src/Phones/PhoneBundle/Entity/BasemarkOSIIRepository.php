<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BasemarkOSIIRepository extends EntityRepository
{
    /**
     * @param BasemarkOSII $stat
     */
    public function save(BasemarkOSII $stat)
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
