<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BatteryLifeRepository extends EntityRepository
{
    /**
     * @param BatteryLife $stat
     */
    public function save(BatteryLife $stat)
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
