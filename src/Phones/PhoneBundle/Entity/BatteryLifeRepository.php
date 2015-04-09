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
                'phoneId'            => $stat->getPhoneId(),
                'provider_id'         => $stat->getProviderId(),
                'original_phone_name' => $stat->getOriginalPhoneName(),
            ];

            $entityRez = $this->findBy($criteria);
            if ($entityRez) {
                /** @var Cost $element */
                foreach ($entityRez as $element) {
                    $this->removeStat($element);
                }
                $em->persist($stat);
                $em->flush($stat);
            } else {
                $em->persist($stat);
                $em->flush($stat);
            }
        }
    }

    /**
     * @param $stat
     */
    public function removeStat($stat)
    {
        $em = $this->getEntityManager();

        $em->remove($stat);
        $em->flush();
    }
}
